<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomInventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hotel_room_inventories';

    protected $guarded = ['id'];

    protected $casts = [
        'check_in'    => 'date',
        'check_out'   => 'date',
        'total_rooms' => 'integer',
        'male_beds'   => 'integer',
        'female_beds' => 'integer',
        'total_beds'  => 'integer',
        'cost_rate'   => 'decimal:2',
        'selling_rate'=> 'decimal:2',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Company::class, 'supplier_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get live room availability for a hotel, room type, and date range.
     *
     * @param int|string|null $hotelId
     * @param string $roomType
     * @param string|null $checkIn (Y-m-d)
     * @param string|null $checkOut (Y-m-d)
     * @param int|null $excludeQuotationId
     * @return array
     */
    public static function getAvailability($hotelId, $roomType, $checkIn = null, $checkOut = null, $excludeQuotationId = null, $gender = null)
    {
        if (!$hotelId) {
            return [
                'has_inventory' => false,
                'is_sharing'    => false,
                'total_stock'   => 0,
                'booked'        => 0,
                'available'     => 999,
                'can_book'      => true,
                'message'       => 'No hotel specified',
            ];
        }

        $isSharing = (strtolower(trim($roomType)) === 'sharing');

        // Check if ANY inventory exists for this hotel & room type
        $hasAnyInventory = self::active()
            ->where('hotel_id', $hotelId)
            ->whereRaw('LOWER(TRIM(room_type)) = ?', [strtolower(trim($roomType))])
            ->exists();

        if (!$hasAnyInventory) {
            return [
                'has_inventory' => false,
                'is_sharing'    => $isSharing,
                'total_stock'   => 0,
                'booked'        => 0,
                'available'     => 999,
                'can_book'      => true,
                'message'       => 'Open stock (No inventory limit defined)',
            ];
        }

        // 1. Find all active inventory records for this hotel and room type
        $invQuery = self::active()
            ->where('hotel_id', $hotelId)
            ->whereRaw('LOWER(TRIM(room_type)) = ?', [strtolower(trim($roomType))]);

        // If dates are provided, filter inventory that covers or overlaps these dates (or perpetual inventories)
        if ($checkIn && $checkOut) {
            $cIn = date('Y-m-d', strtotime($checkIn));
            $cOut = date('Y-m-d', strtotime($checkOut));

            $invQuery->where(function ($q) use ($cIn, $cOut) {
                // Open / perpetual stock
                $q->where(function ($sub) {
                    $sub->whereNull('check_in')->whereNull('check_out');
                })
                // Or date overlapping stock
                ->orWhere(function ($sub) use ($cIn, $cOut) {
                    $sub->where('check_in', '<=', $cOut)
                        ->where('check_out', '>=', $cIn);
                });
            });
        }

        $totalStock = (int) (clone $invQuery)->sum('total_rooms');

        // 2. Calculate booked rooms from active quotations
        $bookedQuery = QuotationAccommodation::whereHas('quotation', function ($q) {
                $q->whereNull('deleted_at')
                  ->whereNotIn('status', ['cancelled', 'rejected']);
            })
            ->where(function ($q) use ($hotelId) {
                $q->where('hotel_id', $hotelId);
                $hotel = Hotel::find($hotelId);
                if ($hotel) {
                    $q->orWhere('hotel_name', $hotel->name);
                }
            })
            ->whereRaw('LOWER(TRIM(room_type)) = ?', [strtolower(trim($roomType))]);

        if ($excludeQuotationId) {
            $bookedQuery->where('quotation_id', '!=', $excludeQuotationId);
        }

        // Overlap with stay dates if check-in & check-out provided
        if ($checkIn && $checkOut) {
            $cIn = date('Y-m-d', strtotime($checkIn));
            $cOut = date('Y-m-d', strtotime($checkOut));

            $bookedQuery->where(function ($q) use ($cIn, $cOut) {
                $q->where(function ($sub) use ($cIn, $cOut) {
                    $sub->whereNotNull('check_in')
                        ->whereNotNull('check_out')
                        ->where('check_in', '<=', $cOut)
                        ->where('check_out', '>=', $cIn);
                })->orWhere(function ($sub) {
                    $sub->whereNull('check_in')->orWhereNull('check_out');
                });
            });
        }

        if ($isSharing) {
            $maleStock = (int) (clone $invQuery)->sum('male_beds');
            $femaleStock = (int) (clone $invQuery)->sum('female_beds');
            $totalBeds = (int) (clone $invQuery)->sum('total_beds');
            if ($totalBeds <= 0 && $totalStock > 0) {
                $totalBeds = $totalStock;
            } elseif ($totalBeds <= 0 && ($maleStock > 0 || $femaleStock > 0)) {
                $totalBeds = $maleStock + $femaleStock;
            }

            $maleBooked = (int) (clone $bookedQuery)->sum('male_beds');
            $femaleBooked = (int) (clone $bookedQuery)->sum('female_beds');
            $totalBedsBooked = (int) (clone $bookedQuery)->sum('no_of_beds');
            if ($totalBedsBooked <= 0) {
                $totalBedsBooked = (int) (clone $bookedQuery)->sum('no_of_rooms');
            }
            if (($maleBooked + $femaleBooked) > $totalBedsBooked) {
                $totalBedsBooked = $maleBooked + $femaleBooked;
            }

            $availMale = max(0, $maleStock - $maleBooked);
            $availFemale = max(0, $femaleStock - $femaleBooked);
            $availTotal = max(0, $totalBeds - $totalBedsBooked);

            $chosenAvail = $availTotal;
            $chosenStock = $totalBeds;
            $chosenBooked = $totalBedsBooked;

            if ($gender === 'male') {
                $chosenAvail = $availMale;
                $chosenStock = $maleStock;
                $chosenBooked = $maleBooked;
            } elseif ($gender === 'female') {
                $chosenAvail = $availFemale;
                $chosenStock = $femaleStock;
                $chosenBooked = $femaleBooked;
            }

            return [
                'has_inventory'     => true,
                'is_sharing'        => true,
                'total_stock'       => $chosenStock,
                'booked'            => $chosenBooked,
                'available'         => $chosenAvail,
                'can_book'          => $chosenAvail > 0,
                'male_stock'        => $maleStock,
                'male_booked'       => $maleBooked,
                'male_available'    => $availMale,
                'female_stock'      => $femaleStock,
                'female_booked'     => $femaleBooked,
                'female_available'  => $availFemale,
                'total_beds'        => $totalBeds,
                'available_beds'    => $availTotal,
                'message'           => "Sharing: {$availTotal} Beds Avail (M: {$availMale}, F: {$availFemale})",
            ];
        }

        $booked = (int) $bookedQuery->sum('no_of_rooms');
        $available = max(0, $totalStock - $booked);

        return [
            'has_inventory' => true,
            'is_sharing'    => false,
            'total_stock'   => $totalStock,
            'booked'        => $booked,
            'available'     => $available,
            'can_book'      => $available > 0,
            'message'       => "{$available} / {$totalStock} rooms available",
        ];
    }

    /**
     * Get room type summary breakdown (Double, Triple, Quad, etc.)
     *
     * @param int|null $hotelId
     * @param string|null $checkIn
     * @param string|null $checkOut
     * @return array
     */
    public static function getHotelSummary($hotelId = null, $checkIn = null, $checkOut = null)
    {
        $types = ['Double', 'Triple', 'Quad', 'Quint', 'Single', 'Sharing', 'Suite'];
        $summary = [];

        foreach ($types as $type) {
            $invQuery = self::active()->whereRaw('LOWER(TRIM(room_type)) = ?', [strtolower($type)]);
            if ($hotelId) {
                $invQuery->where('hotel_id', $hotelId);
            }

            if ($checkIn && $checkOut) {
                $cIn = date('Y-m-d', strtotime($checkIn));
                $cOut = date('Y-m-d', strtotime($checkOut));
                $invQuery->where(function ($q) use ($cIn, $cOut) {
                    $q->where(function ($sub) {
                        $sub->whereNull('check_in')->whereNull('check_out');
                    })->orWhere(function ($sub) use ($cIn, $cOut) {
                        $sub->where('check_in', '<=', $cOut)->where('check_out', '>=', $cIn);
                    });
                });
            }

            $totalStock = (int) $invQuery->sum('total_rooms');

            // Booked query
            $bookedQuery = QuotationAccommodation::whereHas('quotation', function ($q) {
                    $q->whereNull('deleted_at')->whereNotIn('status', ['cancelled', 'rejected']);
                })
                ->whereRaw('LOWER(TRIM(room_type)) = ?', [strtolower($type)]);

            if ($hotelId) {
                $hotel = Hotel::find($hotelId);
                $bookedQuery->where(function ($q) use ($hotelId, $hotel) {
                    $q->where('hotel_id', $hotelId);
                    if ($hotel) {
                        $q->orWhere('hotel_name', $hotel->name);
                    }
                });
            }

            if ($checkIn && $checkOut) {
                $cIn = date('Y-m-d', strtotime($checkIn));
                $cOut = date('Y-m-d', strtotime($checkOut));
                $bookedQuery->where(function ($q) use ($cIn, $cOut) {
                    $q->where(function ($sub) use ($cIn, $cOut) {
                        $sub->whereNotNull('check_in')
                            ->whereNotNull('check_out')
                            ->where('check_in', '<=', $cOut)
                            ->where('check_out', '>=', $cIn);
                    })->orWhere(function ($sub) {
                        $sub->whereNull('check_in')->orWhereNull('check_out');
                    });
                });
            }

            $booked = (int) $bookedQuery->sum('no_of_rooms');
            $available = max(0, $totalStock - $booked);
            $pct = $totalStock > 0 ? min(100, round(($booked / $totalStock) * 100)) : 0;

            $summaryData = [
                'type'        => $type,
                'total_stock' => $totalStock,
                'booked'      => $booked,
                'available'   => $available,
                'percentage'  => $pct,
            ];

            if (strtolower($type) === 'sharing') {
                $maleStock = (int) (clone $invQuery)->sum('male_beds');
                $femaleStock = (int) (clone $invQuery)->sum('female_beds');
                $maleBooked = (int) (clone $bookedQuery)->sum('male_beds');
                $femaleBooked = (int) (clone $bookedQuery)->sum('female_beds');
                $maleAvail = max(0, $maleStock - $maleBooked);
                $femaleAvail = max(0, $femaleStock - $femaleBooked);

                $summaryData['male_stock'] = $maleStock;
                $summaryData['male_booked'] = $maleBooked;
                $summaryData['male_available'] = $maleAvail;
                $summaryData['female_stock'] = $femaleStock;
                $summaryData['female_booked'] = $femaleBooked;
                $summaryData['female_available'] = $femaleAvail;
            }

            $summary[$type] = $summaryData;
        }

        return $summary;
    }
}