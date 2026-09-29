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
    public static function getAvailability($hotelId, $roomType, $checkIn = null, $checkOut = null, $excludeQuotationId = null)
    {
        if (!$hotelId) {
            return [
                'has_inventory' => false,
                'total_stock'   => 0,
                'booked'        => 0,
                'available'     => 999,
                'can_book'      => true,
                'message'       => 'No hotel specified',
            ];
        }

        // Check if ANY inventory exists for this hotel & room type
        $hasAnyInventory = self::active()
            ->where('hotel_id', $hotelId)
            ->whereRaw('LOWER(TRIM(room_type)) = ?', [strtolower(trim($roomType))])
            ->exists();

        if (!$hasAnyInventory) {
            return [
                'has_inventory' => false,
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

        $totalStock = (int) $invQuery->sum('total_rooms');

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

        $booked = (int) $bookedQuery->sum('no_of_rooms');
        $available = max(0, $totalStock - $booked);

        return [
            'has_inventory' => true,
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

            $summary[$type] = [
                'type'        => $type,
                'total_stock' => $totalStock,
                'booked'      => $booked,
                'available'   => $available,
                'percentage'  => $pct,
            ];
        }

        return $summary;
    }
}