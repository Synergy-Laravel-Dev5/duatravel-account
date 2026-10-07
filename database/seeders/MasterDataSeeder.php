<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\Airline;
use App\Models\Vehicle;
use App\Models\Train;
use App\Models\Route;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds for Hotels, Airlines, Vehicles, Trains, and Routes.
     */
    public function run(): void
    {
        $this->seedHotels();
        $this->seedAirlines();
        $this->seedVehicles();
        $this->seedTrains();
        $this->seedRoutes();
    }

    /**
     * Seed Hotels (Makkah & Madinah)
     */
    private function seedHotels(): void
    {
        $hotels = [
            // Makkah Hotels
            [
                'hotel_number'           => 'HTL-MAK-001',
                'code'                   => 'HTL-SWM',
                'name'                   => 'Swissôtel Makkah',
                'address'                => 'King Abdul Aziz Endowment, Abraj Al Bait Complex, Makkah',
                'contact'                => '+966 12 571 8000',
                'email'                  => 'reservations.makkah@swissotel.com',
                'place'                  => 'Makkah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '5 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MAK-002',
                'code'                   => 'HTL-FMT',
                'name'                   => 'Makkah Clock Royal Tower, A Fairmont Hotel',
                'address'                => 'King Abdul Aziz Endowment, Clock Tower, Makkah',
                'contact'                => '+966 12 571 7777',
                'email'                  => 'makkah@fairmont.com',
                'place'                  => 'Makkah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '5 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MAK-003',
                'code'                   => 'HTL-PZM',
                'name'                   => 'Pullman Zamzam Makkah',
                'address'                => 'Abraj Al Bait Complex, Ibrahim Al Khalil St, Makkah',
                'contact'                => '+966 12 571 5555',
                'email'                  => 'h6329@accor.com',
                'place'                  => 'Makkah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '5 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MAK-004',
                'code'                   => 'HTL-MHT',
                'name'                   => 'Makkah Hotel & Towers (Ex-Hilton)',
                'address'                => 'Ibrahim Al Khalil Street, Facing King Fahd Gate, Makkah',
                'contact'                => '+966 12 534 0000',
                'email'                  => 'info@makkah-hotel.com',
                'place'                  => 'Makkah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '5 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MAK-005',
                'code'                   => 'HTL-JOM',
                'name'                   => 'Jabal Omar Marriott Hotel Makkah',
                'address'                => 'Umm Al Qura Road, Jabal Omar, Makkah',
                'contact'                => '+966 12 529 6666',
                'email'                  => 'marriott.makkah@marriott.com',
                'place'                  => 'Makkah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '5 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MAK-006',
                'code'                   => 'HTL-ANJ',
                'name'                   => 'Anjum Hotel Makkah',
                'address'                => 'Umm Al Qura Street, Jabal Kaaba, Makkah',
                'contact'                => '+966 12 510 0000',
                'email'                  => 'reservations@anjumhotels.com',
                'place'                  => 'Makkah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '5 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MAK-007',
                'code'                   => 'HTL-VOC',
                'name'                   => 'voco Makkah, an IHG Hotel',
                'address'                => 'Ibrahim Al Khalil Road, Al Misfalah, Makkah',
                'contact'                => '+966 12 526 0000',
                'email'                  => 'info.vocomakkah@ihg.com',
                'place'                  => 'Makkah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '4 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MAK-008',
                'code'                   => 'HTL-LMD',
                'name'                   => 'Le Méridien Towers Makkah',
                'address'                => 'Kudai Road, Al Aziziyah District, Makkah',
                'contact'                => '+966 12 539 9999',
                'email'                  => 'lemeridien.makkah@marriott.com',
                'place'                  => 'Makkah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '4 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MAK-009',
                'code'                   => 'HTL-SHD',
                'name'                   => 'Al Shohada Hotel Makkah',
                'address'                => 'Ajyad Street, Makkah',
                'contact'                => '+966 12 574 4401',
                'email'                  => 'reservation@alshohada.com',
                'place'                  => 'Makkah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '5 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MAK-010',
                'code'                   => 'HTL-KSW',
                'name'                   => 'Kiswa Towers Hotel',
                'address'                => 'Al Tayseer Street, Jarwal, Makkah',
                'contact'                => '+966 12 541 0000',
                'email'                  => 'info@kiswahotels.com',
                'place'                  => 'Makkah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '3 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MAK-011',
                'code'                   => 'HTL-EMG',
                'name'                   => 'Emaar Grand Hotel Makkah',
                'address'                => 'Ibrahim Al Khalil Street, Al Misfalah, Makkah',
                'contact'                => '+966 12 530 2222',
                'email'                  => 'grand@emaarhotels.com',
                'place'                  => 'Makkah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '4 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MAK-012',
                'code'                   => 'HTL-AZD',
                'name'                   => 'Al Azizia Diamond Hotel',
                'address'                => 'Al Aziziyah North Road, Makkah',
                'contact'                => '+966 12 558 3333',
                'email'                  => 'azizia.diamond@gmail.com',
                'place'                  => 'Azizia',
                'accommodation_type'     => 'Building/Flat',
                'accommodation_category' => '3 Star',
                'status'                 => 'active',
            ],

            // Madinah Hotels
            [
                'hotel_number'           => 'HTL-MED-001',
                'code'                   => 'HTL-OBR',
                'name'                   => 'The Oberoi Madinah',
                'address'                => 'Northern Central Area, Facing Prophet Mosque, Madinah',
                'contact'                => '+966 14 828 2222',
                'email'                  => 'madinah@oberoihotels.com',
                'place'                  => 'Madinah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '5 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MED-002',
                'code'                   => 'HTL-DTQ',
                'name'                   => 'Dar Al Taqwa Hotel Madinah',
                'address'                => 'Off Al Sitteen Street, Facing Ladies Gate, Madinah',
                'contact'                => '+966 14 829 1111',
                'email'                  => 'info@daraltaqwa.com',
                'place'                  => 'Madinah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '5 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MED-003',
                'code'                   => 'HTL-ANM',
                'name'                   => 'Anwar Al Madinah Mövenpick Hotel',
                'address'                => 'Central Zone, Haram Court, Madinah',
                'contact'                => '+966 14 818 1000',
                'email'                  => 'hotel.anwar.reservation@movenpick.com',
                'place'                  => 'Madinah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '5 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MED-004',
                'code'                   => 'HTL-PZM-MDN',
                'name'                   => 'Pullman Zamzam Madinah',
                'address'                => 'Amr Bin Al Aas Street, Al Badaah, Madinah',
                'contact'                => '+966 14 821 0500',
                'email'                  => 'h6329-re@accor.com',
                'place'                  => 'Madinah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '5 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MED-005',
                'code'                   => 'HTL-FAL',
                'name'                   => 'Frontel Al Harithia Hotel Madinah',
                'address'                => 'Central Northern Area, Madinah',
                'contact'                => '+966 14 820 0111',
                'email'                  => 'reservation@frontelhotels.com',
                'place'                  => 'Madinah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '5 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MED-006',
                'code'                   => 'HTL-EMR',
                'name'                   => 'Emaar Royal Hotel Al Madina',
                'address'                => 'North Central Area, Al Badaah, Madinah',
                'contact'                => '+966 14 819 5555',
                'email'                  => 'royal@emaarhotels.com',
                'place'                  => 'Madinah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '4 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MED-007',
                'code'                   => 'HTL-TBF',
                'name'                   => 'Taiba Front Hotel Madinah',
                'address'                => 'Northern Central Zone, Madinah',
                'contact'                => '+966 14 829 0055',
                'email'                  => 'info@taibafronthotel.com',
                'place'                  => 'Madinah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '4 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MED-008',
                'code'                   => 'HTL-RVA',
                'name'                   => 'Ruve Al Madinah Hotel',
                'address'                => 'Abi Ayyub Al-Ansari St, Central Area, Madinah',
                'contact'                => '+966 14 829 9999',
                'email'                  => 'info@ruvealmadinah.com',
                'place'                  => 'Madinah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '4 Star',
                'status'                 => 'active',
            ],
            [
                'hotel_number'           => 'HTL-MED-009',
                'code'                   => 'HTL-RDA',
                'name'                   => 'Rawda Al Aqeeq Hotel',
                'address'                => 'Western Central Area, As Salam St, Madinah',
                'contact'                => '+966 14 820 4444',
                'email'                  => 'rawda@alaqeeq.com',
                'place'                  => 'Madinah',
                'accommodation_type'     => 'Hotel',
                'accommodation_category' => '4 Star',
                'status'                 => 'active',
            ],
        ];

        foreach ($hotels as $hotelData) {
            Hotel::updateOrCreate(
                ['code' => $hotelData['code']],
                $hotelData
            );
        }
    }

    /**
     * Seed Airlines
     */
    private function seedAirlines(): void
    {
        $airlines = [
            [
                'name'      => 'Pakistan International Airlines',
                'code'      => 'PK-PIA',
                'iata_code' => 'PK',
                'icao_code' => 'PIA',
                'country'   => 'Pakistan',
                'call_sign' => 'PAKISTAN',
                'status'    => 'active',
            ],
            [
                'name'      => 'Saudia (Saudi Arabian Airlines)',
                'code'      => 'SV-SVA',
                'iata_code' => 'SV',
                'icao_code' => 'SVA',
                'country'   => 'Saudi Arabia',
                'call_sign' => 'SAUDIA',
                'status'    => 'active',
            ],
            [
                'name'      => 'Flynas',
                'code'      => 'XY-KNE',
                'iata_code' => 'XY',
                'icao_code' => 'KNE',
                'country'   => 'Saudi Arabia',
                'call_sign' => 'NAS EXPRESS',
                'status'    => 'active',
            ],
            [
                'name'      => 'Flyadeal',
                'code'      => 'F3-FAD',
                'iata_code' => 'F3',
                'icao_code' => 'FAD',
                'country'   => 'Saudi Arabia',
                'call_sign' => 'ADEAL',
                'status'    => 'active',
            ],
            [
                'name'      => 'Airblue',
                'code'      => 'PA-ABQ',
                'iata_code' => 'PA',
                'icao_code' => 'ABQ',
                'country'   => 'Pakistan',
                'call_sign' => 'AIRBLUE',
                'status'    => 'active',
            ],
            [
                'name'      => 'Serene Air',
                'code'      => 'ER-SEP',
                'iata_code' => 'ER',
                'icao_code' => 'SEP',
                'country'   => 'Pakistan',
                'call_sign' => 'SERENE',
                'status'    => 'active',
            ],
            [
                'name'      => 'Fly Jinnah',
                'code'      => '9P-FJL',
                'iata_code' => '9P',
                'icao_code' => 'FJL',
                'country'   => 'Pakistan',
                'call_sign' => 'FLY JINNAH',
                'status'    => 'active',
            ],
            [
                'name'      => 'Emirates Airline',
                'code'      => 'EK-UAE',
                'iata_code' => 'EK',
                'icao_code' => 'UAE',
                'country'   => 'United Arab Emirates',
                'call_sign' => 'EMIRATES',
                'status'    => 'active',
            ],
            [
                'name'      => 'Flydubai',
                'code'      => 'FZ-FDB',
                'iata_code' => 'FZ',
                'icao_code' => 'FDB',
                'country'   => 'United Arab Emirates',
                'call_sign' => 'FLYDUBAI',
                'status'    => 'active',
            ],
            [
                'name'      => 'Air Arabia',
                'code'      => 'G9-ABY',
                'iata_code' => 'G9',
                'icao_code' => 'ABY',
                'country'   => 'United Arab Emirates',
                'call_sign' => 'ARABIA',
                'status'    => 'active',
            ],
            [
                'name'      => 'Qatar Airways',
                'code'      => 'QR-QTR',
                'iata_code' => 'QR',
                'icao_code' => 'QTR',
                'country'   => 'Qatar',
                'call_sign' => 'QATARI',
                'status'    => 'active',
            ],
            [
                'name'      => 'Gulf Air',
                'code'      => 'GF-GFA',
                'iata_code' => 'GF',
                'icao_code' => 'GFA',
                'country'   => 'Bahrain',
                'call_sign' => 'GULF AIR',
                'status'    => 'active',
            ],
            [
                'name'      => 'Etihad Airways',
                'code'      => 'EY-ETD',
                'iata_code' => 'EY',
                'icao_code' => 'ETD',
                'country'   => 'United Arab Emirates',
                'call_sign' => 'ETIHAD',
                'status'    => 'active',
            ],
            [
                'name'      => 'Oman Air',
                'code'      => 'WY-OMA',
                'iata_code' => 'WY',
                'icao_code' => 'OMA',
                'country'   => 'Oman',
                'call_sign' => 'OMAN AIR',
                'status'    => 'active',
            ],
            [
                'name'      => 'SalamAir',
                'code'      => 'OV-OMS',
                'iata_code' => 'OV',
                'icao_code' => 'OMS',
                'country'   => 'Oman',
                'call_sign' => 'SALAM AIR',
                'status'    => 'active',
            ],
            [
                'name'      => 'Kuwait Airways',
                'code'      => 'KU-KAC',
                'iata_code' => 'KU',
                'icao_code' => 'KAC',
                'country'   => 'Kuwait',
                'call_sign' => 'KUWAITI',
                'status'    => 'active',
            ],
            [
                'name'      => 'Turkish Airlines',
                'code'      => 'TK-THY',
                'iata_code' => 'TK',
                'icao_code' => 'THY',
                'country'   => 'Turkey',
                'call_sign' => 'TURKISH',
                'status'    => 'active',
            ],
            [
                'name'      => 'Jazeera Airways',
                'code'      => 'J9-JZR',
                'iata_code' => 'J9',
                'icao_code' => 'JZR',
                'country'   => 'Kuwait',
                'call_sign' => 'JAZEERA',
                'status'    => 'active',
            ],
        ];

        foreach ($airlines as $airlineData) {
            Airline::updateOrCreate(
                ['code' => $airlineData['code']],
                $airlineData
            );
        }
    }

    /**
     * Seed Vehicles
     */
    private function seedVehicles(): void
    {
        $vehicles = [
            [
                'vehicle_type'  => 'Luxury SUV (GMC Yukon)',
                'brand_name'    => 'GMC Yukon XL',
                'model_year'    => '2024',
                'plate_number'  => '4521-KSA',
                'supplier_name' => 'Al-Haram VIP Transport',
                'status'        => 'active',
            ],
            [
                'vehicle_type'  => 'Private Car (Sedan)',
                'brand_name'    => 'Toyota Camry',
                'model_year'    => '2023',
                'plate_number'  => '9402-KSA',
                'supplier_name' => 'Dua Fleet Transport',
                'status'        => 'active',
            ],
            [
                'vehicle_type'  => 'Family SUV (7 Seater)',
                'brand_name'    => 'Toyota Fortuner',
                'model_year'    => '2024',
                'plate_number'  => '1823-KSA',
                'supplier_name' => 'Al-Wifaq Transport',
                'status'        => 'active',
            ],
            [
                'vehicle_type'  => 'Family MPV (7-8 Seater)',
                'brand_name'    => 'Hyundai Staria / H1',
                'model_year'    => '2024',
                'plate_number'  => '6109-KSA',
                'supplier_name' => 'Rahhal Express Transport',
                'status'        => 'active',
            ],
            [
                'vehicle_type'  => 'Passenger Van (14 Seater)',
                'brand_name'    => 'Toyota HiAce Grandia',
                'model_year'    => '2023',
                'plate_number'  => '8812-KSA',
                'supplier_name' => 'Makkah Coach Service',
                'status'        => 'active',
            ],
            [
                'vehicle_type'  => 'Mini Bus (30 Seater)',
                'brand_name'    => 'Toyota Coaster',
                'model_year'    => '2024',
                'plate_number'  => '3190-KSA',
                'supplier_name' => 'Al-Qaid Group Transport',
                'status'        => 'active',
            ],
            [
                'vehicle_type'  => 'VIP Coach Bus (49 Seater)',
                'brand_name'    => 'Mercedes-Benz Travego',
                'model_year'    => '2024',
                'plate_number'  => '7724-KSA',
                'supplier_name' => 'SAPTCO VIP Fleet',
                'status'        => 'active',
            ],
            [
                'vehicle_type'  => 'Standard Coach (50 Seater)',
                'brand_name'    => 'Yutong High Decker',
                'model_year'    => '2023',
                'plate_number'  => '5541-KSA',
                'supplier_name' => 'Dabbab Transport Syndicate',
                'status'        => 'active',
            ],
        ];

        foreach ($vehicles as $vehData) {
            Vehicle::updateOrCreate(
                ['plate_number' => $vehData['plate_number']],
                $vehData
            );
        }
    }

    /**
     * Seed Trains
     */
    private function seedTrains(): void
    {
        $trains = [
            [
                'name'   => 'Haramain High Speed Railway (HHR Express)',
                'code'   => 'HHR-01',
                'status' => 'active',
            ],
            [
                'name'   => 'Haramain Bullet Train - Business Class',
                'code'   => 'HHR-BIZ',
                'status' => 'active',
            ],
            [
                'name'   => 'Haramain Bullet Train - Economy Class',
                'code'   => 'HHR-ECO',
                'status' => 'active',
            ],
            [
                'name'   => 'Al-Mashaaer Al-Mugaddassah Metro (Hajj Holy Sites Train)',
                'code'   => 'MMM-HAJJ',
                'status' => 'active',
            ],
            [
                'name'   => 'SAR North-South Passenger Railway',
                'code'   => 'SAR-NSP',
                'status' => 'active',
            ],
            [
                'name'   => 'Riyadh - Dammam Intercity Train',
                'code'   => 'SRO-RD',
                'status' => 'active',
            ],
        ];

        foreach ($trains as $trainData) {
            Train::updateOrCreate(
                ['code' => $trainData['code']],
                $trainData
            );
        }
    }

    /**
     * Seed Routes
     */
    private function seedRoutes(): void
    {
        $routes = [
            // KSA Airport & Intercity Transfers
            [
                'start_place' => 'Jeddah Airport (JED)',
                'end_place'   => 'Makkah Hotel',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Makkah Hotel',
                'end_place'   => 'Madinah Hotel',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Madinah Hotel',
                'end_place'   => 'Makkah Hotel',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Madinah Hotel',
                'end_place'   => 'Madinah Airport (MED)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Madinah Hotel',
                'end_place'   => 'Jeddah Airport (JED)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Makkah Hotel',
                'end_place'   => 'Jeddah Airport (JED)',
                'status'      => 'active',
            ],

            // Ziyarat Routes
            [
                'start_place' => 'Makkah Hotel',
                'end_place'   => 'Ziyarat Makkah (Jabal Al-Noor, Thowr, Mina, Arafat)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Madinah Hotel',
                'end_place'   => 'Ziyarat Madinah (Masjid Quba, Qiblatain, Uhud)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Makkah Hotel',
                'end_place'   => 'Jeddah City Tour & Corniche',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Makkah Hotel',
                'end_place'   => 'Taif Ziyarat & Cable Car',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Makkah Hotel',
                'end_place'   => 'Haramain Train Station (Rusaifah)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Madinah Hotel',
                'end_place'   => 'Haramain Train Station (Knowledge City)',
                'status'      => 'active',
            ],

            // Hajj Holy Sites Movement
            [
                'start_place' => 'Makkah Hotel',
                'end_place'   => 'Mina Camps (8 Dhul Hijjah)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Mina Camps',
                'end_place'   => 'Arafat Plain (9 Dhul Hijjah)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Arafat Plain',
                'end_place'   => 'Muzdalifah Night Stay',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Muzdalifah',
                'end_place'   => 'Mina Jamarat Stoning',
                'status'      => 'active',
            ],

            // Flight Flight Routes (Origin -> Saudi)
            [
                'start_place' => 'Karachi (KHI)',
                'end_place'   => 'Jeddah (JED)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Karachi (KHI)',
                'end_place'   => 'Madinah (MED)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Lahore (LHE)',
                'end_place'   => 'Jeddah (JED)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Lahore (LHE)',
                'end_place'   => 'Madinah (MED)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Islamabad (ISB)',
                'end_place'   => 'Jeddah (JED)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Islamabad (ISB)',
                'end_place'   => 'Madinah (MED)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Multan (MUX)',
                'end_place'   => 'Jeddah (JED)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Sialkot (SKT)',
                'end_place'   => 'Jeddah (JED)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Peshawar (PEW)',
                'end_place'   => 'Jeddah (JED)',
                'status'      => 'active',
            ],
            [
                'start_place' => 'Quetta (UET)',
                'end_place'   => 'Jeddah (JED)',
                'status'      => 'active',
            ],
        ];

        foreach ($routes as $routeData) {
            Route::updateOrCreate(
                [
                    'start_place' => $routeData['start_place'],
                    'end_place'   => $routeData['end_place'],
                ],
                $routeData
            );
        }
    }
}
