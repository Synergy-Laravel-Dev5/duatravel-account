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
        if (Schema::hasTable('booking_transports')) {
            Schema::table('booking_transports', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_transports', 'vehicle')) {
                    $table->string('vehicle', 100)->nullable()->after('route');
                }
                if (!Schema::hasColumn('booking_transports', 'quantity')) {
                    $table->integer('quantity')->default(1)->after('vehicle');
                }
                if (!Schema::hasColumn('booking_transports', 'status')) {
                    $table->string('status', 50)->default('CONFIRMED')->after('quantity');
                }
                if (!Schema::hasColumn('booking_transports', 'pax')) {
                    $table->integer('pax')->default(1)->after('status');
                }
                if (!Schema::hasColumn('booking_transports', 'pickup_from')) {
                    $table->string('pickup_from', 150)->nullable()->after('flight_no');
                }
                if (!Schema::hasColumn('booking_transports', 'destination')) {
                    $table->string('destination', 150)->nullable()->after('pickup_from');
                }
                if (!Schema::hasColumn('booking_transports', 'brn')) {
                    $table->string('brn', 100)->nullable()->after('destination');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('booking_transports')) {
            Schema::table('booking_transports', function (Blueprint $table) {
                foreach (['vehicle', 'quantity', 'status', 'pax', 'pickup_from', 'destination', 'brn'] as $col) {
                    if (Schema::hasColumn('booking_transports', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
