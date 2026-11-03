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
        Schema::table('booking_persons', function (Blueprint $table) {
            $table->string('trip_type', 30)->default('round_trip')->after('flight_time');
            $table->string('departure_ticket', 20)->nullable()->after('trip_type');
            $table->decimal('departure_cost', 15, 2)->default(0)->after('departure_ticket');
            $table->decimal('departure_sell', 15, 2)->default(0)->after('departure_cost');
            $table->string('return_ticket', 20)->nullable()->after('departure_sell');
            $table->decimal('return_cost', 15, 2)->default(0)->after('return_ticket');
            $table->decimal('return_sell', 15, 2)->default(0)->after('return_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_persons', function (Blueprint $table) {
            $table->dropColumn([
                'trip_type',
                'departure_ticket',
                'departure_cost',
                'departure_sell',
                'return_ticket',
                'return_cost',
                'return_sell',
            ]);
        });
    }
};
