<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('booking_transports')) {
            Schema::table('booking_transports', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_transports', 'travel_date')) {
                    $table->date('travel_date')->nullable()->after('route');
                }
                if (!Schema::hasColumn('booking_transports', 'travel_time')) {
                    $table->string('travel_time', 20)->nullable()->after('travel_date');
                }
                if (!Schema::hasColumn('booking_transports', 'flight_no')) {
                    $table->string('flight_no', 100)->nullable()->after('travel_time');
                }
                if (!Schema::hasColumn('booking_transports', 'airline')) {
                    $table->string('airline', 100)->nullable()->after('flight_no');
                }
            });
        }

        if (Schema::hasTable('booking_hotels')) {
            try {
                DB::statement("ALTER TABLE `booking_hotels` MODIFY COLUMN `no_of_rooms` INT(11) NOT NULL DEFAULT 1");
                DB::statement("ALTER TABLE `booking_hotels` MODIFY COLUMN `no_of_nights` INT(11) NOT NULL DEFAULT 1");
            } catch (\Throwable $e) {
                // Ignore if modifying column fails on specific DB engine
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('booking_transports')) {
            Schema::table('booking_transports', function (Blueprint $table) {
                foreach (['travel_date', 'travel_time', 'flight_no', 'airline'] as $col) {
                    if (Schema::hasColumn('booking_transports', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
