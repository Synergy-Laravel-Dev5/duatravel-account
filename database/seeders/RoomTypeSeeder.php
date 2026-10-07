<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;

class RoomTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roomTypes = [
            [
                'name'        => 'Single',
                'code'        => 'single',
                'capacity'    => 1,
                'description' => 'Single Bed Room for 1 Person',
                'status'      => 'active',
            ],
            [
                'name'        => 'Double',
                'code'        => 'double',
                'capacity'    => 2,
                'description' => '2 Bed / Double Room for 2 Persons',
                'status'      => 'active',
            ],
            [
                'name'        => 'Triple',
                'code'        => 'triple',
                'capacity'    => 3,
                'description' => '3 Bed Room for 3 Persons',
                'status'      => 'active',
            ],
            [
                'name'        => 'Quad',
                'code'        => 'quad',
                'capacity'    => 4,
                'description' => '4 Bed Room for 4 Persons',
                'status'      => 'active',
            ],
            [
                'name'        => 'Quint',
                'code'        => 'quint',
                'capacity'    => 5,
                'description' => '5 Bed Room for 5 Persons',
                'status'      => 'active',
            ],
            [
                'name'        => 'Sharing',
                'code'        => 'sharing',
                'capacity'    => 4,
                'description' => 'Standard Sharing Room / Multiple Beds',
                'status'      => 'active',
            ],
            [
                'name'        => 'Suite',
                'code'        => 'suite',
                'capacity'    => 2,
                'description' => 'Junior / Standard Suite Room',
                'status'      => 'active',
            ],
            [
                'name'        => 'Executive Suite',
                'code'        => 'executive_suite',
                'capacity'    => 2,
                'description' => 'Luxury Executive Suite Room',
                'status'      => 'active',
            ],
            [
                'name'        => 'Family Room',
                'code'        => 'family_room',
                'capacity'    => 4,
                'description' => 'Spacious Family Connected Room',
                'status'      => 'active',
            ],
            [
                'name'        => 'Studio',
                'code'        => 'studio',
                'capacity'    => 2,
                'description' => 'Self-contained Studio Apartment',
                'status'      => 'active',
            ],
        ];

        foreach ($roomTypes as $type) {
            RoomType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
