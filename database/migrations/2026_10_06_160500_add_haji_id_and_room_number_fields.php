<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('booking_persons')) {
            Schema::table('booking_persons', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_persons', 'haji_id')) {
                    $table->string('haji_id')->nullable()->after('full_name');
                }
                if (!Schema::hasColumn('booking_persons', 'room_number')) {
                    $table->string('room_number')->nullable()->after('passport_number');
                }
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'haji_id')) {
                    $table->string('haji_id')->nullable()->after('client_id');
                }
                if (!Schema::hasColumn('bookings', 'room_number')) {
                    $table->string('room_number')->nullable()->after('passport_number');
                }
            });
        }

        if (Schema::hasTable('hotel_room_inventories')) {
            Schema::table('hotel_room_inventories', function (Blueprint $table) {
                if (!Schema::hasColumn('hotel_room_inventories', 'room_numbers')) {
                    $table->string('room_numbers')->nullable()->after('total_rooms');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('booking_persons')) {
            Schema::table('booking_persons', function (Blueprint $table) {
                if (Schema::hasColumn('booking_persons', 'haji_id')) {
                    $table->dropColumn('haji_id');
                }
                if (Schema::hasColumn('booking_persons', 'room_number')) {
                    $table->dropColumn('room_number');
                }
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (Schema::hasColumn('bookings', 'haji_id')) {
                    $table->dropColumn('haji_id');
                }
                if (Schema::hasColumn('bookings', 'room_number')) {
                    $table->dropColumn('room_number');
                }
            });
        }

        if (Schema::hasTable('hotel_room_inventories')) {
            Schema::table('hotel_room_inventories', function (Blueprint $table) {
                if (Schema::hasColumn('hotel_room_inventories', 'room_numbers')) {
                    $table->dropColumn('room_numbers');
                }
            });
        }
    }
};
