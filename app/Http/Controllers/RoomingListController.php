<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingPerson;
use App\Models\Hotel;
use App\Models\HotelRoomCapacity;
use App\Models\RoomType;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomingListController extends Controller
{
    /**
     * Rooming List & Bed Allocation Report (Matching Official Manifest Format)
     */
    public function index(Request $request)
    {
        $data = $this->getFilteredRoomingData($request);

        // Dropdown Data
        $registeredHotels = Hotel::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        $distinctHotelNames = BookingHotel::select('hotel_name')->distinct()->whereNotNull('hotel_name')->pluck('hotel_name');
        $roomTypes = RoomType::where('status', 'active')->orderBy('capacity')->get();
        if ($roomTypes->isEmpty()) {
            $roomTypes = collect([
                (object)['name' => 'Single', 'code' => 'single', 'capacity' => 1],
                (object)['name' => 'Double', 'code' => 'double', 'capacity' => 2],
                (object)['name' => 'Triple', 'code' => 'triple', 'capacity' => 3],
                (object)['name' => 'Quad', 'code' => 'quad', 'capacity' => 4],
                (object)['name' => 'Quint', 'code' => 'quint', 'capacity' => 5],
                (object)['name' => 'Six', 'code' => 'six', 'capacity' => 6],
                (object)['name' => 'Sharing', 'code' => 'sharing', 'capacity' => 4],
                (object)['name' => 'Suite', 'code' => 'suite', 'capacity' => 2],
            ]);
        }

        return view('reports.rooming_list', array_merge($data, compact(
            'registeredHotels',
            'distinctHotelNames',
            'roomTypes'
        )));
    }

    /**
     * Export Official Rooming List & Manifest as PDF
     */
    public function exportPdf(Request $request)
    {
        $data = $this->getFilteredRoomingData($request);

        $pdf = Pdf::loadView('reports.rooming_list_pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $hotelPart = !empty($data['hotelFilter']) ? '_' . preg_replace('/[^A-Za-z0-9]/', '_', $data['hotelFilter']) : '';
        $locPart = (!empty($data['locationFilter']) && $data['locationFilter'] !== 'all') ? '_' . strtoupper($data['locationFilter']) : '';
        $fileName = 'Official_Rooming_List_Manifest' . $locPart . $hotelPart . '_' . date('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Export Official Rooming List & Manifest as Excel (CSV with UTF-8 BOM for MS Excel)
     */
    public function exportExcel(Request $request)
    {
        $data = $this->getFilteredRoomingData($request);
        $groupedRooms = $data['groupedRooms'];

        $hotelPart = !empty($data['hotelFilter']) ? '_' . preg_replace('/[^A-Za-z0-9]/', '_', $data['hotelFilter']) : '';
        $locPart = (!empty($data['locationFilter']) && $data['locationFilter'] !== 'all') ? '_' . strtoupper($data['locationFilter']) : '';
        $fileName = 'Official_Rooming_List_Manifest' . $locPart . $hotelPart . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($groupedRooms) {
            $handle = fopen('php://output', 'w');
            
            // Output UTF-8 BOM so Microsoft Excel cleanly renders characters
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Column Headers
            fputcsv($handle, [
                'SR',
                'HAJJ ID',
                'HB NUMBER',
                'PASSPORT NUMBER',
                'FULL NAME',
                'GENDER',
                'LOCATION',
                'HOTEL / BUILDING',
                'ROOM TYPE',
                'ROOM NUMBER',
                'TOTAL BEDS',
                'OCCUPIED BEDS',
                'AVAILABLE BEDS',
                'ROOM GENDER',
                'BOOKING NUMBER',
                'CLIENT / GROUP NAME',
                'PHONE NUMBER',
                'CHECK IN',
                'CHECK OUT',
                'ROOM NOTES'
            ]);

            $sr = 1;
            foreach ($groupedRooms as $room) {
                $rNum = trim($room['room_number']);
                $isUnassigned = ($rNum === 'PENDING' || $rNum === 'UNASSIGNED' || empty($rNum));
                $roomDisplayNo = $isUnassigned ? 'UNASSIGNED' : (str_starts_with(strtoupper($rNum), 'R') ? strtoupper($rNum) : ('R' . $rNum));
                $locLabel = strtoupper($room['location'] ?? 'MAKKAH');

                if (empty($room['occupants'])) {
                    fputcsv($handle, [
                        $sr++,
                        '—',
                        '—',
                        '—',
                        '(Empty Room)',
                        '—',
                        $locLabel,
                        $room['hotel_name'],
                        $room['room_type'],
                        $roomDisplayNo,
                        $room['total_capacity'],
                        0,
                        $room['total_capacity'],
                        $room['room_gender'] ?? 'Any',
                        '—',
                        '—',
                        '—',
                        $room['check_in_min'] ? date('d M Y', strtotime($room['check_in_min'])) : '—',
                        $room['check_out_max'] ? date('d M Y', strtotime($room['check_out_max'])) : '—',
                        $room['notes'] ?? ''
                    ]);
                } else {
                    foreach ($room['occupants'] as $occ) {
                        fputcsv($handle, [
                            $sr++,
                            !empty($occ['hajj_id']) ? $occ['hajj_id'] : '—',
                            !empty($occ['hb_number']) ? $occ['hb_number'] : '—',
                            $occ['passport'] ?? '—',
                            $occ['name'] ?? '—',
                            strtoupper($occ['gender'] ?? 'Male'),
                            $locLabel,
                            $room['hotel_name'],
                            $room['room_type'],
                            $roomDisplayNo,
                            $room['total_capacity'],
                            $room['occupied_beds'],
                            $room['available_beds'],
                            $room['room_gender'] ?? 'Any',
                            $occ['booking_number'] ?? '—',
                            $occ['client_name'] ?? '—',
                            $occ['phone'] ?? '—',
                            $occ['check_in'] ?? '—',
                            $occ['check_out'] ?? '—',
                            $room['notes'] ?? ''
                        ]);
                    }
                }
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Bulk Assign Selected Pilgrims (via Checkboxes) to a Room
     */
    public function assignRoom(Request $request)
    {
        $request->validate([
            'person_ids'   => 'required|array|min:1',
            'hotel_name'   => 'required|string|max:191',
            'room_number'  => 'nullable|string|max:100',
            'room_type'    => 'nullable|string|max:100',
            'location'     => 'nullable|string|max:100',
            'gender'       => 'nullable|string|max:50',
        ]);

        $personIds  = $request->person_ids;
        $hotelName  = trim($request->hotel_name);
        $roomNumber = !empty($request->room_number) ? trim($request->room_number) : null;
        $roomType   = $request->room_type ? trim($request->room_type) : 'Quad';
        $location   = $request->location ? trim($request->location) : 'makkah';
        $gender     = $request->gender ? trim($request->gender) : 'Any';

        $updatedCount = 0;
        foreach ($personIds as $pid) {
            if (is_numeric($pid)) {
                $person = BookingPerson::find($pid);
                if ($person) {
                    $person->room_number = $roomNumber;
                    $person->room_type = $roomType;
                    $person->hotel_name = $hotelName;
                    $person->location = $location;
                    $person->room_gender = $gender;
                    $person->save();
                    $updatedCount++;

                    // Sync/Ensure BookingHotel on this booking
                    $bHotel = BookingHotel::where('booking_id', $person->booking_id)
                        ->where(function ($q) use ($location, $hotelName) {
                            $q->where('location', $location)->orWhere('hotel_name', $hotelName);
                        })->first();

                    if ($bHotel) {
                        if (!empty($roomNumber) || empty($bHotel->room_number)) {
                            $bHotel->room_number = $roomNumber;
                            $bHotel->room_type = strtolower($roomType);
                            $bHotel->save();
                        }
                    } else {
                        BookingHotel::create([
                            'booking_id'   => $person->booking_id,
                            'location'     => $location,
                            'hotel_name'   => $hotelName,
                            'room_type'    => strtolower($roomType),
                            'room_number'  => $roomNumber,
                            'gender'       => $gender,
                            'no_of_nights' => 1,
                            'no_of_rooms'  => 1,
                        ]);
                    }
                }
            }
        }

        // Maintain capacity in hotel_room_capacities if room number is provided
        if (!empty($roomNumber)) {
            $capRecord = HotelRoomCapacity::whereRaw('LOWER(TRIM(hotel_name)) = ?', [strtolower($hotelName)])
                ->whereRaw('LOWER(TRIM(room_number)) = ?', [strtolower($roomNumber)])
                ->first();

            if (!$capRecord) {
                $baseCap = HotelRoomCapacity::resolveCapacity($hotelName, $roomNumber, $roomType, $location);
                $capRecord = new HotelRoomCapacity();
                $capRecord->hotel_name = $hotelName;
                $capRecord->room_number = $roomNumber;
                $capRecord->room_type = $roomType;
                $capRecord->location = $location;
                $capRecord->gender = $gender;
                $capRecord->bed_capacity = max(count($personIds), $baseCap);
                $capRecord->save();
            } elseif ($capRecord->bed_capacity < count($personIds)) {
                $capRecord->bed_capacity = count($personIds);
                $capRecord->save();
            }
        }

        $successMsg = !empty($roomNumber) 
            ? "{$updatedCount} Pilgrims assigned to Room {$roomNumber} ({$hotelName} - {$roomType}) successfully!"
            : "{$updatedCount} Pilgrims allocated to {$hotelName} ({$roomType}) successfully!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
            ]);
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Bulk Unassign Selected Pilgrims from Rooms (Move back to Pending)
     */
    public function unassignRoom(Request $request)
    {
        $request->validate([
            'person_ids' => 'required|array|min:1',
        ]);

        $personIds = $request->person_ids;
        $unassignedCount = 0;

        foreach ($personIds as $pid) {
            if (is_numeric($pid)) {
                $person = BookingPerson::find($pid);
                if ($person) {
                    $person->room_number = null;
                    $person->room_type = null;
                    $person->hotel_name = null;
                    $person->save();
                    $unassignedCount++;
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$unassignedCount} Pilgrims moved to Pending Room Allocation.",
            ]);
        }

        return back()->with('success', "{$unassignedCount} Pilgrims moved to Pending Room Allocation.");
    }

    /**
     * Shared filter logic for Web view, PDF export, and Excel export
     * (Includes ALL pilgrims from ALL active bookings)
     */
    private function getFilteredRoomingData(Request $request): array
    {
        $hotelFilter    = $request->get('hotel_name');
        $locationFilter = $request->get('location');
        $roomTypeFilter = $request->get('room_type');
        $statusFilter   = $request->get('status'); // all, available, full, overbooked, unassigned
        $search         = $request->get('search');
        $fromDate       = $request->get('from_date');
        $toDate         = $request->get('to_date');
        $viewMode       = $request->get('view', 'manifest');

        // Query active bookings (Rooming List & Manifest Report is strictly for Hajj - Umrah bookings are excluded)
        $bookingQuery = Booking::with([
            'client',
            'company',
            'persons',
            'hotels'
        ])->whereNull('deleted_at')
          ->whereNotIn('status', ['cancelled', 'rejected'])
          ->where(function ($q) {
              $q->whereNull('package_type')
                ->orWhereRaw('LOWER(TRIM(package_type)) != ?', ['umrah']);
          });

        if (!empty($search)) {
            $term = '%' . $search . '%';
            $bookingQuery->where(function ($bq) use ($term) {
                $bq->where('voucher_number', 'LIKE', $term)
                   ->orWhere('passport_number', 'LIKE', $term)
                   ->orWhereHas('client', function ($cq) use ($term) {
                       $cq->where('name', 'LIKE', $term)
                          ->orWhere('passport_number', 'LIKE', $term);
                   })
                   ->orWhereHas('persons', function ($pq) use ($term) {
                       $pq->where('full_name', 'LIKE', $term)
                          ->orWhere('passport_number', 'LIKE', $term)
                          ->orWhere('cnic', 'LIKE', $term)
                          ->orWhere('haji_id', 'LIKE', $term)
                          ->orWhere('hb_number', 'LIKE', $term)
                          ->orWhere('room_number', 'LIKE', $term);
                   })
                   ->orWhereHas('hotels', function ($hq) use ($term) {
                       $hq->where('room_number', 'LIKE', $term)
                          ->orWhere('hotel_name', 'LIKE', $term);
                   });
            });
        }

        $allBookings = $bookingQuery->latest()->get();

        $groupedRooms = [];
        $unassignedOccupants = [];

        foreach ($allBookings as $b) {
            $persons = $b->persons;
            
            // If booking has no persons created yet, generate virtual representation
            if (!$persons || $persons->count() === 0) {
                $pax = max(1, (int) ($b->no_of_pax ?: 1));
                $persons = collect();
                for ($i = 1; $i <= $pax; $i++) {
                    $p = new BookingPerson([
                        'booking_id'      => $b->id,
                        'full_name'       => ($b->client->name ?? 'Guest') . ($pax > 1 ? " (#{$i})" : ''),
                        'passport_number' => $b->passport_number ?: '—',
                        'gender'          => 'Male',
                        'hb_number'       => 'HB' . str_pad($b->id * 10 + $i, 6, '0', STR_PAD_LEFT),
                    ]);
                    $p->id = 'temp_' . $b->id . '_' . $i;
                    $persons->push($p);
                }
            }

            foreach ($persons as $p) {
                $hajjId = !empty($p->haji_id) ? trim($p->haji_id) : '';
                $hbNumber = !empty($p->hb_number) ? trim($p->hb_number) : '';
                $gender = !empty($p->gender) ? ucfirst(strtolower($p->gender)) : 'Male';

                if (empty($p->gender) || $p->gender === 'Male') {
                    $fCheck = strtolower(($p->full_name ?? '') . ' ' . ($p->given_name ?? '') . ' ' . ($p->surname ?? ''));
                    if (preg_match('/\b(bibi|khatoon|fatima|ayesha|aisha|maryam|begum|bano|naz|uzma|zahra|zainab|amna|rabia|samina|sumaira|hira|sana|madiha|tooba|javeria|rubab|shagufta|roshan|safia)\b/i', $fCheck)) {
                        $gender = 'Female';
                    }
                }

                // Check person level room or booking hotel level room
                $rNum = !empty($p->room_number) ? trim($p->room_number) : null;
                $rType = !empty($p->room_type) ? trim($p->room_type) : null;
                $hName = !empty($p->hotel_name) ? trim($p->hotel_name) : null;
                $loc = !empty($p->location) ? trim($p->location) : null;
                $rGender = !empty($p->room_gender) ? trim($p->room_gender) : 'Any';
                $cIn = null;
                $cOut = null;

                if (empty($rNum) && $b->hotels && $b->hotels->count() > 0) {
                    $matchedHotel = null;
                    if (!empty($locationFilter) && $locationFilter !== 'all') {
                        $matchedHotel = $b->hotels->firstWhere('location', $locationFilter);
                    }
                    if (!$matchedHotel) {
                        $matchedHotel = $b->hotels->first(fn($h) => !empty($h->room_number));
                    }
                    if (!$matchedHotel) {
                        $matchedHotel = $b->hotels->first();
                    }

                    if ($matchedHotel) {
                        $rNum = !empty($matchedHotel->room_number) ? trim($matchedHotel->room_number) : null;
                        $rType = $rType ?: $matchedHotel->room_type;
                        $hName = $hName ?: $matchedHotel->hotel_name;
                        $loc = $loc ?: $matchedHotel->location;
                        $cIn = $matchedHotel->check_in;
                        $cOut = $matchedHotel->check_out;
                    }
                }

                // Determine Booked Room Type from booking / hotel / pax count
                $bookedRoomType = 'Quad';
                $bHotelFirst = $b->hotels ? $b->hotels->first() : null;
                if (!empty($p->room_type)) {
                    $bookedRoomType = ucfirst(trim($p->room_type));
                } elseif ($bHotelFirst && !empty($bHotelFirst->room_type)) {
                    $bookedRoomType = ucfirst(trim($bHotelFirst->room_type));
                } elseif (!empty($b->package?->room_type)) {
                    $bookedRoomType = ucfirst(trim($b->package->room_type));
                } else {
                    $pax = max(1, (int) ($b->no_of_pax ?: 1));
                    $bookedRoomType = match($pax) {
                        1 => 'Single',
                        2 => 'Double',
                        3 => 'Triple',
                        4 => 'Quad',
                        5 => 'Quint',
                        6 => 'Six / Sharing',
                        default => 'Sharing (' . $pax . ' Beds)',
                    };
                }
                if (in_array(strtolower($bookedRoomType), ['six', 'sharing', '6', 'six-bed', '6-bed'])) {
                    $bookedRoomType = 'Six / Sharing';
                }

                $occData = [
                    'person_id'         => $p->id,
                    'real_person_id'    => is_numeric($p->id) ? (int)$p->id : null,
                    'hajj_id'           => $hajjId,
                    'hb_number'         => $hbNumber,
                    'name'              => $p->full_name ?: trim(($p->given_name ?? '') . ' ' . ($p->surname ?? 'Pilgrim')),
                    'passport'          => $p->passport_number ?: '—',
                    'gender'            => $gender,
                    'photo'             => $p->photo,
                    'cnic'              => $p->cnic,
                    'phone'             => $p->phone ?: $b->phone,
                    'booking_id'        => $b->id,
                    'booking_number'    => $b->booking_number ?? ('#BK-' . $b->id),
                    'client_name'       => $b->client->name ?? ($b->company->company_name ?? ($b->company->name ?? 'N/A')),
                    'check_in'          => $cIn ? date('d M Y', strtotime($cIn)) : '—',
                    'check_out'         => $cOut ? date('d M Y', strtotime($cOut)) : '—',
                    'room_type'         => $rType ?: 'Quad',
                    'booked_room_type'  => $bookedRoomType,
                    'booked_hotel_name' => $bHotelFirst ? $bHotelFirst->hotel_name : '—',
                    'booking_pax'       => $b->no_of_pax ?: 1,
                    'room_number'       => $rNum,
                    'hotel_name'        => $hName,
                    'location'          => $loc ?: 'makkah',
                ];

                if (empty($rNum)) {
                    $unassignedOccupants[] = $occData;
                } else {
                    $hName = $hName ?: 'Unspecified Hotel';
                    $rType = ucfirst($rType ?: 'Quad');
                    $loc = $loc ?: 'makkah';

                    $groupKey = strtolower($loc . '___' . $hName . '___' . $rNum);

                    if (!isset($groupedRooms[$groupKey])) {
                        $baseCap = HotelRoomCapacity::resolveCapacity($hName, $rNum, $rType, $loc);
                        $customCap = HotelRoomCapacity::where(function ($q) use ($rNum) {
                            foreach (HotelRoomCapacity::normalizeRoomNumber($rNum) as $v) {
                                $q->orWhereRaw('LOWER(TRIM(room_number)) = ?', [$v]);
                            }
                        })->first();

                        $extraBeds = $customCap ? (int) $customCap->extra_beds : 0;
                        $effectiveCap = max(1, $baseCap);
                        $roomGender = !empty($customCap->gender) && $customCap->gender !== 'Any' ? $customCap->gender : $rGender;

                        $groupedRooms[$groupKey] = [
                            'hotel_name'      => $hName,
                            'room_number'     => $rNum,
                            'room_type'       => $rType,
                            'room_gender'     => $roomGender,
                            'location'        => $loc,
                            'capacity_id'     => $customCap ? $customCap->id : null,
                            'base_capacity'   => $baseCap,
                            'extra_beds'      => $extraBeds,
                            'total_capacity'  => $effectiveCap,
                            'occupied_beds'   => 0,
                            'available_beds'  => $effectiveCap,
                            'is_full'         => false,
                            'is_overbooked'   => false,
                            'notes'           => $customCap ? $customCap->notes : null,
                            'occupants'       => [],
                            'check_in_min'    => $cIn,
                            'check_out_max'   => $cOut,
                            'booking_ids'     => [],
                        ];
                    }

                    $groupedRooms[$groupKey]['occupied_beds']++;
                    if (!in_array($b->id, $groupedRooms[$groupKey]['booking_ids'])) {
                        $groupedRooms[$groupKey]['booking_ids'][] = $b->id;
                    }
                    $groupedRooms[$groupKey]['occupants'][] = $occData;
                }
            }
        }

        // Apply filters to assigned rooms
        if (!empty($hotelFilter)) {
            $groupedRooms = array_filter($groupedRooms, fn($r) => stripos($r['hotel_name'], $hotelFilter) !== false);
        }
        if (!empty($locationFilter) && $locationFilter !== 'all') {
            $groupedRooms = array_filter($groupedRooms, fn($r) => strtolower($r['location']) === strtolower($locationFilter));
        }
        if (!empty($roomTypeFilter) && $roomTypeFilter !== 'all') {
            $cleanType = strtolower(trim($roomTypeFilter));
            $groupedRooms = array_filter($groupedRooms, function ($r) use ($cleanType) {
                $rt = strtolower(trim($r['room_type']));
                if ($cleanType === 'six') return in_array($rt, ['six', 'sharing']);
                return $rt === $cleanType;
            });
        }

        // Finalize occupancy flags & stats for assigned rooms
        $totalRoomsInUse = 0;
        $totalBedsCapacity = 0;
        $totalBedsOccupied = 0;
        $totalBedsAvailable = 0;
        $fullRoomsCount = 0;
        $partialRoomsCount = 0;
        $overbookedRoomsCount = 0;

        foreach ($groupedRooms as $key => &$room) {
            $cap = $room['total_capacity'];
            $occ = $room['occupied_beds'];

            $room['available_beds'] = max(0, $cap - $occ);
            $room['is_full'] = ($occ >= $cap && $cap > 0);
            $room['is_overbooked'] = ($occ > $cap);

            $totalRoomsInUse++;
            $totalBedsCapacity += $cap;
            $totalBedsOccupied += $occ;
            $totalBedsAvailable += $room['available_beds'];

            if ($room['is_overbooked']) {
                $overbookedRoomsCount++;
            } elseif ($room['is_full']) {
                $fullRoomsCount++;
            } else {
                $partialRoomsCount++;
            }
        }
        unset($room);

        // Filter by Status if requested
        if (!empty($statusFilter) && $statusFilter !== 'all') {
            if ($statusFilter === 'unassigned') {
                $groupedRooms = [];
            } else {
                $groupedRooms = array_filter($groupedRooms, function ($room) use ($statusFilter) {
                    if ($statusFilter === 'available') {
                        return !$room['is_full'] && !$room['is_overbooked'];
                    } elseif ($statusFilter === 'full') {
                        return $room['is_full'] && !$room['is_overbooked'];
                    } elseif ($statusFilter === 'overbooked') {
                        return $room['is_overbooked'];
                    }
                    return true;
                });
            }
        }

        $unassignedPilgrimsCount = count($unassignedOccupants);

        // If there are unassigned pilgrims, append them as a special section at the beginning/end
        if ($unassignedPilgrimsCount > 0 && (empty($statusFilter) || $statusFilter === 'all' || $statusFilter === 'unassigned')) {
            $groupedRooms['__pending_allocation__'] = [
                'hotel_name'      => 'Pending Room Allocation',
                'room_number'     => 'PENDING',
                'room_type'       => 'Pending Allocation',
                'room_gender'     => 'Any',
                'location'        => 'unassigned',
                'capacity_id'     => null,
                'base_capacity'   => $unassignedPilgrimsCount,
                'extra_beds'      => 0,
                'total_capacity'  => $unassignedPilgrimsCount,
                'occupied_beds'   => $unassignedPilgrimsCount,
                'available_beds'  => 0,
                'is_full'         => false,
                'is_overbooked'   => false,
                'notes'           => 'Pilgrims awaiting room assignment',
                'occupants'       => $unassignedOccupants,
                'check_in_min'    => null,
                'check_out_max'   => null,
                'booking_ids'     => array_unique(array_column($unassignedOccupants, 'booking_id')),
            ];
            $totalBedsOccupied += $unassignedPilgrimsCount;
        }

        return compact(
            'groupedRooms',
            'unassignedOccupants',
            'unassignedPilgrimsCount',
            'totalRoomsInUse',
            'totalBedsCapacity',
            'totalBedsOccupied',
            'totalBedsAvailable',
            'fullRoomsCount',
            'partialRoomsCount',
            'overbookedRoomsCount',
            'hotelFilter',
            'locationFilter',
            'roomTypeFilter',
            'statusFilter',
            'search',
            'fromDate',
            'toDate',
            'viewMode'
        );
    }

    /**
     * Adjust Bed Capacity for a Room (Increase beds, add extra beds)
     */
    public function adjustBed(Request $request)
    {
        $request->validate([
            'hotel_name'   => 'required|string|max:191',
            'room_number'  => 'required|string|max:100',
            'bed_capacity' => 'required|integer|min:1|max:50',
            'extra_beds'   => 'nullable|integer|min:0|max:20',
            'notes'        => 'nullable|string|max:500',
        ]);

        $hotelName = trim($request->hotel_name);
        $roomNumber = trim($request->room_number);
        $bedCapacity = (int) $request->bed_capacity;
        $extraBeds = (int) ($request->extra_beds ?? 0);
        $roomType = $request->room_type ? trim($request->room_type) : null;
        $location = $request->location ? trim($request->location) : null;
        $gender = $request->gender ? trim($request->gender) : 'Any';

        $record = HotelRoomCapacity::whereRaw('LOWER(TRIM(hotel_name)) = ?', [strtolower($hotelName)])
            ->whereRaw('LOWER(TRIM(room_number)) = ?', [strtolower($roomNumber)])
            ->first();

        if (!$record) {
            $record = new HotelRoomCapacity();
            $record->hotel_name = $hotelName;
            $record->room_number = $roomNumber;
        }

        $record->room_type = $roomType;
        $record->location = $location;
        $record->gender = $gender;
        $record->bed_capacity = $bedCapacity;
        $record->extra_beds = $extraBeds;
        $record->notes = $request->notes;
        $record->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Bed capacity for Room {$roomNumber} ({$hotelName}) updated to {$bedCapacity} beds successfully!",
                'data'    => $record,
            ]);
        }

        return back()->with('success', "Bed capacity for Room {$roomNumber} ({$hotelName}) updated to {$bedCapacity} beds successfully!");
    }

    /**
     * Real-time Live Capacity Check API for Booking Create & Edit
     */
    public function checkCapacityApi(Request $request)
    {
        $hotelName = $request->get('hotel_name');
        $roomNumber = $request->get('room_number');
        $roomType = $request->get('room_type');
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');
        $excludeBookingId = $request->get('booking_id');
        $location = $request->get('location');

        if (empty($roomNumber) || (empty($hotelName) && empty($location))) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide hotel name and room number.',
            ]);
        }

        $occupancy = HotelRoomCapacity::getRoomOccupancy(
            $hotelName,
            $roomNumber,
            $roomType,
            $checkIn,
            $checkOut,
            $excludeBookingId,
            $location
        );

        $cap = $occupancy['capacity'];
        $occ = $occupancy['occupied_beds'];
        $avail = $occupancy['available_beds'];
        $isFull = $occupancy['is_full'];
        $isOver = $occupancy['is_overbooked'];

        $occupantsNames = array_column($occupancy['occupants'], 'name');
        $summaryNames = implode(', ', array_slice($occupantsNames, 0, 3));
        if (count($occupantsNames) > 3) {
            $summaryNames .= ' +' . (count($occupantsNames) - 3) . ' more';
        }

        $message = '';
        if ($isOver) {
            $message = "⚠️ OVERBOOKED: Room {$roomNumber} has {$occ}/{$cap} beds allocated ({$summaryNames}). No more pilgrims can be assigned to this room.";
        } elseif ($isFull) {
            $message = "🔴 ROOM IS FULL: Room {$roomNumber} is at maximum capacity ({$occ}/{$cap} beds occupied by {$summaryNames}). You cannot add more pilgrims to this room unless bed capacity is increased in the Rooming List.";
        } elseif ($occ > 0) {
            $message = "🟡 Room {$roomNumber}: {$occ}/{$cap} beds occupied ({$avail} beds available). Current occupants: {$summaryNames}.";
        } else {
            // Check if current booking itself occupies this room (Edit Mode clarity)
            if ($excludeBookingId) {
                $selfOcc = HotelRoomCapacity::getRoomOccupancy($hotelName, $roomNumber, $roomType, $checkIn, $checkOut, null, $location);
                if ($selfOcc['occupied_beds'] > 0) {
                    $selfNames = implode(', ', array_slice(array_column($selfOcc['occupants'], 'name'), 0, 3));
                    $message = "🔵 Room {$roomNumber}: {$selfOcc['occupied_beds']}/{$cap} beds assigned to this current booking ({$selfNames}).";
                } else {
                    $message = "🟢 Room {$roomNumber}: Fully available ({$cap} total beds).";
                }
            } else {
                $message = "🟢 Room {$roomNumber}: Fully available ({$cap} total beds).";
            }
        }

        return response()->json([
            'success'           => true,
            'room_number'       => $roomNumber,
            'hotel_name'        => $hotelName,
            'location'          => $location,
            'room_type'         => $roomType,
            'capacity'          => $cap,
            'occupied'          => $occ,
            'available'         => $avail,
            'is_full'           => $isFull,
            'is_overbooked'     => $isOver,
            'occupants_count'   => $occupancy['occupants_count'],
            'occupants_summary' => $summaryNames,
            'message'           => $message,
        ]);
    }
}
