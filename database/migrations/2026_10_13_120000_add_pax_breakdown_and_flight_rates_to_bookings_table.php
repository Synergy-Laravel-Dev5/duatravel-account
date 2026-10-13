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
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'adults_count')) {
                $table->integer('adults_count')->default(1)->after('no_of_pax');
            }
            if (!Schema::hasColumn('bookings', 'children_count')) {
                $table->integer('children_count')->default(0)->after('adults_count');
            }
            if (!Schema::hasColumn('bookings', 'infants_count')) {
                $table->integer('infants_count')->default(0)->after('children_count');
            }
            if (!Schema::hasColumn('bookings', 'flight_adult_cost')) {
                $table->decimal('flight_adult_cost', 12, 2)->default(0)->after('flight_selling_rate');
            }
            if (!Schema::hasColumn('bookings', 'flight_adult_sell')) {
                $table->decimal('flight_adult_sell', 12, 2)->default(0)->after('flight_adult_cost');
            }
            if (!Schema::hasColumn('bookings', 'flight_child_cost')) {
                $table->decimal('flight_child_cost', 12, 2)->default(0)->after('flight_adult_sell');
            }
            if (!Schema::hasColumn('bookings', 'flight_child_sell')) {
                $table->decimal('flight_child_sell', 12, 2)->default(0)->after('flight_child_cost');
            }
            if (!Schema::hasColumn('bookings', 'flight_infant_cost')) {
                $table->decimal('flight_infant_cost', 12, 2)->default(0)->after('flight_child_sell');
            }
            if (!Schema::hasColumn('bookings', 'flight_infant_sell')) {
                $table->decimal('flight_infant_sell', 12, 2)->default(0)->after('flight_infant_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $cols = [
                'adults_count', 'children_count', 'infants_count',
                'flight_adult_cost', 'flight_adult_sell',
                'flight_child_cost', 'flight_child_sell',
                'flight_infant_cost', 'flight_infant_sell'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('bookings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
