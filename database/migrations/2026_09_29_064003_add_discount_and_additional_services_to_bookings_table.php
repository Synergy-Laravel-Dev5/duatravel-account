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
            $table->decimal('discount', 12, 2)->nullable()->default(0)->after('other_charges');
            $table->text('additional_services_detail')->nullable()->after('discount');
            $table->decimal('additional_services_amount', 12, 2)->nullable()->default(0)->after('additional_services_detail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['discount', 'additional_services_detail', 'additional_services_amount']);
        });
    }
};
