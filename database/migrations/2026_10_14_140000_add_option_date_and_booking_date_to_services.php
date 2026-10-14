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
        // 1. Bookings Table (Flight Booking & Option Date)
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'flight_booking_date')) {
                    $table->date('flight_booking_date')->nullable()->after('flight_supplier_id');
                }
                if (!Schema::hasColumn('bookings', 'flight_option_date')) {
                    $table->date('flight_option_date')->nullable()->after('flight_booking_date');
                }
            });
        }

        // 2. Booking Hotels Table (Option Date & Booking Date)
        if (Schema::hasTable('booking_hotels')) {
            Schema::table('booking_hotels', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_hotels', 'option_date')) {
                    $table->date('option_date')->nullable()->after('check_out');
                }
                if (!Schema::hasColumn('booking_hotels', 'booking_date')) {
                    $table->date('booking_date')->nullable()->after('option_date');
                }
            });
        }

        // 3. Booking Transports Table (Booking Date & Option Date)
        if (Schema::hasTable('booking_transports')) {
            Schema::table('booking_transports', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_transports', 'booking_date')) {
                    $table->date('booking_date')->nullable()->after('transport_type');
                }
                if (!Schema::hasColumn('booking_transports', 'option_date')) {
                    $table->date('option_date')->nullable()->after('booking_date');
                }
            });
        }

        // 4. Booking Visas Table (Booking Date & Option Date)
        if (Schema::hasTable('booking_visas')) {
            Schema::table('booking_visas', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_visas', 'booking_date')) {
                    $table->date('booking_date')->nullable()->after('send_to');
                }
                if (!Schema::hasColumn('booking_visas', 'option_date')) {
                    $table->date('option_date')->nullable()->after('booking_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (Schema::hasColumn('bookings', 'flight_booking_date')) {
                    $table->dropColumn('flight_booking_date');
                }
                if (Schema::hasColumn('bookings', 'flight_option_date')) {
                    $table->dropColumn('flight_option_date');
                }
            });
        }

        if (Schema::hasTable('booking_hotels')) {
            Schema::table('booking_hotels', function (Blueprint $table) {
                if (Schema::hasColumn('booking_hotels', 'option_date')) {
                    $table->dropColumn('option_date');
                }
                if (Schema::hasColumn('booking_hotels', 'booking_date')) {
                    $table->dropColumn('booking_date');
                }
            });
        }

        if (Schema::hasTable('booking_transports')) {
            Schema::table('booking_transports', function (Blueprint $table) {
                if (Schema::hasColumn('booking_transports', 'booking_date')) {
                    $table->dropColumn('booking_date');
                }
                if (Schema::hasColumn('booking_transports', 'option_date')) {
                    $table->dropColumn('option_date');
                }
            });
        }

        if (Schema::hasTable('booking_visas')) {
            Schema::table('booking_visas', function (Blueprint $table) {
                if (Schema::hasColumn('booking_visas', 'booking_date')) {
                    $table->dropColumn('booking_date');
                }
                if (Schema::hasColumn('booking_visas', 'option_date')) {
                    $table->dropColumn('option_date');
                }
            });
        }
    }
};
