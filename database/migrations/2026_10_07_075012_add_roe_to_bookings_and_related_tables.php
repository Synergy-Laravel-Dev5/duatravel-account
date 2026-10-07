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
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'flight_roe')) {
                    $table->decimal('flight_roe', 12, 4)->default(1.0000)->after('flight_supplier_id');
                }
            });
        }

        if (Schema::hasTable('booking_hotels')) {
            Schema::table('booking_hotels', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_hotels', 'roe')) {
                    $table->decimal('roe', 12, 4)->default(1.0000)->after('supplier_id');
                }
            });
        }

        if (Schema::hasTable('booking_transports')) {
            Schema::table('booking_transports', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_transports', 'roe')) {
                    $table->decimal('roe', 12, 4)->default(1.0000)->after('supplier_id');
                }
            });
        }

        if (Schema::hasTable('booking_visas')) {
            Schema::table('booking_visas', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_visas', 'roe')) {
                    $table->decimal('roe', 12, 4)->default(1.0000)->after('supplier_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'flight_roe')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('flight_roe');
            });
        }

        if (Schema::hasTable('booking_hotels') && Schema::hasColumn('booking_hotels', 'roe')) {
            Schema::table('booking_hotels', function (Blueprint $table) {
                $table->dropColumn('roe');
            });
        }

        if (Schema::hasTable('booking_transports') && Schema::hasColumn('booking_transports', 'roe')) {
            Schema::table('booking_transports', function (Blueprint $table) {
                $table->dropColumn('roe');
            });
        }

        if (Schema::hasTable('booking_visas') && Schema::hasColumn('booking_visas', 'roe')) {
            Schema::table('booking_visas', function (Blueprint $table) {
                $table->dropColumn('roe');
            });
        }
    }
};
