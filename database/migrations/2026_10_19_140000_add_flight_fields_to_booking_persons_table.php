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
                if (!Schema::hasColumn('booking_persons', 'pax_type')) {
                    $table->string('pax_type', 20)->default('adult')->after('full_name');
                }
                if (!Schema::hasColumn('booking_persons', 'flight_ticket')) {
                    $table->string('flight_ticket', 100)->nullable()->after('passport_number');
                }
                if (!Schema::hasColumn('booking_persons', 'flight_cost')) {
                    $table->decimal('flight_cost', 15, 2)->default(0)->after('flight_ticket');
                }
                if (!Schema::hasColumn('booking_persons', 'flight_sell')) {
                    $table->decimal('flight_sell', 15, 2)->default(0)->after('flight_cost');
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
                foreach (['pax_type', 'flight_ticket', 'flight_cost', 'flight_sell'] as $col) {
                    if (Schema::hasColumn('booking_persons', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
