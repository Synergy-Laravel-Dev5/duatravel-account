<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {

            if (Schema::hasColumn('bookings', 'airline')) {
                $table->dropColumn('airline');
            }

            if (Schema::hasColumn('bookings', 'pnr_ticket')) {
                $table->dropColumn('pnr_ticket');
            }

            if (!Schema::hasColumn('bookings', 'departure_airline')) {
                $table->string('departure_airline')->nullable()->after('departure_time');
            }

            if (!Schema::hasColumn('bookings', 'departure_pnr')) {
                $table->string('departure_pnr')->nullable()->after('departure_airline');
            }

            if (!Schema::hasColumn('bookings', 'arrival_airline')) {
                $table->string('arrival_airline')->nullable()->after('arrival_time');
            }

            if (!Schema::hasColumn('bookings', 'arrival_pnr')) {
                $table->string('arrival_pnr')->nullable()->after('arrival_airline');
            }
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['departure_airline', 'departure_pnr', 'arrival_airline', 'arrival_pnr']);
            $table->string('airline')->nullable();
            $table->string('pnr_ticket')->nullable();
        });
    }
};
