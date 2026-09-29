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
    Schema::table('packages', function (Blueprint $table) {

        $table->string('package_type')->nullable()->after('category');
        $table->decimal('package_amount', 12, 2)->nullable()->after('package_type');
        $table->string('food_included')->nullable()->after('package_amount');
        $table->decimal('ticket_amount', 12, 2)->nullable()->after('food_included');
        $table->string('extra_facilities')->nullable()->after('ticket_amount');
        $table->decimal('extra_facilities_amount', 12, 2)->nullable()->after('extra_facilities');
        $table->string('other_extra_facilities')->nullable()->after('extra_facilities_amount');
        $table->string('qurbani_included')->nullable()->after('other_extra_facilities');
        $table->string('ticket_included')->nullable()->after('qurbani_included');
        $table->string('place_of_departure')->nullable()->after('ticket_included');
        $table->decimal('air_ticket_amount', 12, 2)->nullable()->after('place_of_departure');
        $table->string('flight_type')->nullable()->after('air_ticket_amount');
        $table->string('flight_class')->nullable()->after('flight_type');

    });
}

public function down(): void
{
    Schema::table('packages', function (Blueprint $table) {



    });
}

    /**
     * Reverse the migrations.
     */

};
