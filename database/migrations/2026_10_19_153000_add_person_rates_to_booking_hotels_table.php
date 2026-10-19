<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_hotels', 'person_rates')) {
                $table->text('person_rates')->nullable()->after('selling_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_hotels', function (Blueprint $table) {
            if (Schema::hasColumn('booking_hotels', 'person_rates')) {
                $table->dropColumn('person_rates');
            }
        });
    }
};
