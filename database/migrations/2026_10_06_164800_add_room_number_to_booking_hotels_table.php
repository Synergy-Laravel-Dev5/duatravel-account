<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_hotels', 'room_number')) {
                $table->string('room_number')->nullable()->after('no_of_rooms');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_hotels', function (Blueprint $table) {
            if (Schema::hasColumn('booking_hotels', 'room_number')) {
                $table->dropColumn('room_number');
            }
        });
    }
};
