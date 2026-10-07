<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelRoomCapacity extends Model
{
    use HasFactory;

    protected $table = 'hotel_room_capacities';

    protected $fillable = [
        'hotel_id',
        'hotel_name',
        'location',
        'room_number',
        'room_type',
        'gender',
        'bed_capacity',
        'extra_beds',
        'notes',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Standard capacity map for room types if not found in database.
     */
    public static function standardTypeCapacity(string $type = null): int
    {
        $normalized = strtolower(trim((string) $type));

        if (empty($normalized)) {
            return 2;
        }

        // Check if defined in room_types table
        $roomType = RoomType::whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
            ->orWhereRaw('LOWER(TRIM(code)) = ?', [$normalized])
            ->first();

        if ($roomType && $roomType->capacity > 0) {
            return (int) $roomType->capacity;
        }

        return match ($normalized) {
            'single'            => 1,
            'double'            => 2,
            'triple'            => 3,
            'quad'              => 4,
            'quint'             => 5,
            'sharing'           => 4,
            'suite'             => 2,
            'executive_suite'   => 2,
            'family_room'       => 4,
            'studio'            => 2,
            default             => 2,
        };
    }

    /**
     * Normalize hotel string for resilient matching across spelling variations, unicode, and suffixes.
     */
    public static function normalizeHotelString(?string $str): string
    {
        if (empty($str)) return '';
        $s = mb_strtolower(trim($str), 'UTF-8');
        $s = strtr($s, [
            'ô' => 'o', 'ö' => 'o', 'ò' => 'o', 'ó' => 'o',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        ]);
        $s = preg_replace('/\s*\([^)]*\)/', '', $s);
        $s = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }

    /**
     * Generate room number variants (e.g. '101' and 'r101').
     */
    public static function normalizeRoomNumber(?string $roomNumber): array
    {
        $clean = strtolower(trim((string) $roomNumber));
        if (empty($clean)) return [];

        $variants = [$clean];
        if (str_starts_with($clean, 'r')) {
            $variants[] = ltrim($clean, 'r');
        } else {
            $variants[] = 'r' . $clean;
        }
        return array_unique(array_filter($variants));
    }

    /**
     * Resolve effective bed capacity for a room in a hotel.
     * Looks up custom capacity in hotel_room_capacities; if absent, uses room_type standard capacity.
     */
    public static function resolveCapacity(?string $hotelName, ?string $roomNumber, ?string $roomType = null, ?string $location = null): int
    {
        $normHotel = self::normalizeHotelString($hotelName);
        $roomVariants = self::normalizeRoomNumber($roomNumber);
        $cleanLoc = strtolower(trim((string) $location));

        if (!empty($roomVariants)) {
            $customs = self::where(function ($q) use ($roomVariants) {
                foreach ($roomVariants as $v) {
                    $q->orWhereRaw('LOWER(TRIM(room_number)) = ?', [$v]);
                }
            })->get();

            foreach ($customs as $custom) {
                $cNorm = self::normalizeHotelString($custom->hotel_name);
                $cLoc = strtolower(trim((string) $custom->location));

                $isMatch = false;
                if (!empty($normHotel) && !empty($cNorm) && ($cNorm === $normHotel || str_contains($cNorm, $normHotel) || str_contains($normHotel, $cNorm))) {
                    $isMatch = true;
                } elseif (!empty($cleanLoc) && !empty($cLoc) && $cleanLoc === $cLoc) {
                    $isMatch = true;
                }

                if ($isMatch && $custom->bed_capacity > 0) {
                    return (int) $custom->bed_capacity;
                }
            }
        }

        return self::standardTypeCapacity($roomType);
    }

    /**
     * Get detailed occupancy info for a specific hotel & room number.
     * Calculates occupied beds from active bookings, available beds, full/overbooked flags, and occupants list.
     */
    public static function getRoomOccupancy(?string $hotelName, ?string $roomNumber, ?string $roomType = null, $checkIn = null, $checkOut = null, $excludeBookingId = null, ?string $location = null): array
    {
        $normHotel = self::normalizeHotelString($hotelName);
        $roomVariants = self::normalizeRoomNumber($roomNumber);
        $cleanLoc = strtolower(trim((string) $location));

        $capacity = self::resolveCapacity($hotelName, $roomNumber, $roomType, $location);

        if (empty($roomVariants)) {
            return [
                'capacity'         => $capacity,
                'occupied_beds'    => 0,
                'available_beds'   => $capacity,
                'is_full'          => false,
                'is_overbooked'    => false,
                'occupants_count'  => 0,
                'occupants'        => [],
                'booking_count'    => 0,
            ];
        }

        // Query active BookingHotel records matching room number variants
        $query = BookingHotel::with([
            'booking.client',
            'booking.company',
            'booking.persons',
        ])->whereHas('booking', function ($q) use ($excludeBookingId) {
            $q->whereNull('deleted_at')->whereNotIn('status', ['cancelled', 'rejected']);
            if ($excludeBookingId) {
                $q->where('id', '!=', $excludeBookingId);
            }
        })->where(function ($q) use ($roomVariants) {
            foreach ($roomVariants as $v) {
                $q->orWhereRaw('LOWER(TRIM(room_number)) = ?', [$v]);
            }
        });

        // Check date overlap if dates provided
        if (!empty($checkIn) && !empty($checkOut)) {
            $cIn = date('Y-m-d', strtotime(str_replace('/', '-', $checkIn)));
            $cOut = date('Y-m-d', strtotime(str_replace('/', '-', $checkOut)));
            $query->where(function ($q) use ($cIn, $cOut) {
                $q->where(function ($sub) {
                    $sub->whereNull('check_in')->whereNull('check_out');
                })->orWhere(function ($sub) use ($cIn, $cOut) {
                    $sub->where('check_in', '<=', $cOut)->where('check_out', '>=', $cIn);
                });
            });
        }

        $allHotels = $query->get();

        // Match hotel name or location
        $bookingHotels = $allHotels->filter(function ($bh) use ($normHotel, $cleanLoc) {
            $bhNorm = self::normalizeHotelString($bh->hotel_name);
            $bhLoc = strtolower(trim((string) $bh->location));

            // 1. Direct normalized match
            if (!empty($normHotel) && !empty($bhNorm) && ($bhNorm === $normHotel || str_contains($bhNorm, $normHotel) || str_contains($normHotel, $bhNorm))) {
                return true;
            }

            // 2. Keyword match (e.g. "swissotel", "pullman", "anjum", "buildin", "camp")
            $firstWord = explode(' ', $normHotel)[0] ?? '';
            if (strlen($firstWord) >= 4 && str_contains($bhNorm, $firstWord)) {
                return true;
            }

            // 3. Location match (e.g. Azizia Building or Mina Camp)
            if (!empty($cleanLoc) && !empty($bhLoc) && $cleanLoc === $bhLoc) {
                if (in_array($cleanLoc, ['azizia', 'mina', 'arafat'])) {
                    return true;
                }
                if (!empty($normHotel) && !empty($bhNorm) && soundex($normHotel) === soundex($bhNorm)) {
                    return true;
                }
            }

            // If no location or hotel string provided on either side, match by room number
            if (empty($normHotel) && empty($cleanLoc)) {
                return true;
            }

            return false;
        });

        $occupiedBeds = 0;
        $occupants = [];
        $seenBookingIds = [];

        foreach ($bookingHotels as $bh) {
            $b = $bh->booking;
            if (!$b) continue;

            $seenBookingIds[$b->id] = true;

            $persons = $b->persons;
            if ($persons && $persons->count() > 0) {
                foreach ($persons as $p) {
                    $occupiedBeds++;
                    $occupants[] = [
                        'person_id'      => $p->id,
                        'name'           => $p->full_name ?: trim(($p->given_name ?? '') . ' ' . ($p->surname ?? 'Pilgrim')),
                        'passport'       => $p->passport_number,
                        'cnic'           => $p->cnic,
                        'phone'          => $p->phone ?: $b->phone,
                        'booking_id'     => $b->id,
                        'booking_number' => $b->booking_number ?? ('#BK-' . $b->id),
                        'client_name'    => $b->client->name ?? ($b->company->company_name ?? 'N/A'),
                        'check_in'       => $bh->check_in ? date('d M Y', strtotime($bh->check_in)) : '—',
                        'check_out'      => $bh->check_out ? date('d M Y', strtotime($bh->check_out)) : '—',
                        'room_type'      => $bh->room_type,
                    ];
                }
            } else {
                $pax = max(1, (int) ($b->no_of_pax ?: 1));
                $occupiedBeds += $pax;
                for ($i = 1; $i <= $pax; $i++) {
                    $occupants[] = [
                        'person_id'      => null,
                        'name'           => ($b->client->name ?? 'Guest') . " (Pax #{$i})",
                        'passport'       => $b->passport_number,
                        'cnic'           => $b->cnic,
                        'phone'          => $b->phone,
                        'booking_id'     => $b->id,
                        'booking_number' => $b->booking_number ?? ('#BK-' . $b->id),
                        'client_name'    => $b->client->name ?? ($b->company->company_name ?? 'N/A'),
                        'check_in'       => $bh->check_in ? date('d M Y', strtotime($bh->check_in)) : '—',
                        'check_out'      => $bh->check_out ? date('d M Y', strtotime($bh->check_out)) : '—',
                        'room_type'      => $bh->room_type,
                    ];
                }
            }
        }

        $availableBeds = max(0, $capacity - $occupiedBeds);
        $isFull = ($occupiedBeds >= $capacity && $capacity > 0);
        $isOverbooked = ($occupiedBeds > $capacity);

        return [
            'capacity'         => $capacity,
            'occupied_beds'    => $occupiedBeds,
            'available_beds'   => $availableBeds,
            'is_full'          => $isFull,
            'is_overbooked'    => $isOverbooked,
            'occupants_count'  => count($occupants),
            'occupants'        => $occupants,
            'booking_count'    => count($seenBookingIds),
        ];
    }
}
