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
        // 1. Bookings Table
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'flight_currency')) {
                    $table->string('flight_currency', 20)->default('SAR')->after('flight_supplier_id');
                }
                if (!Schema::hasColumn('bookings', 'flight_cost_roe')) {
                    $table->decimal('flight_cost_roe', 12, 4)->default(75.0000)->after('flight_currency');
                }
                if (!Schema::hasColumn('bookings', 'flight_sale_roe')) {
                    $table->decimal('flight_sale_roe', 12, 4)->default(75.0000)->after('flight_cost_roe');
                }
                if (!Schema::hasColumn('bookings', 'discount')) {
                    $table->decimal('discount', 12, 2)->default(0)->after('other_charges');
                }
                if (!Schema::hasColumn('bookings', 'additional_services_amount')) {
                    $table->decimal('additional_services_amount', 15, 2)->default(0)->after('discount');
                }
                if (!Schema::hasColumn('bookings', 'additional_services_detail')) {
                    $table->text('additional_services_detail')->nullable()->after('additional_services_amount');
                }
            });
        }

        // 2. Booking Hotels Table
        if (Schema::hasTable('booking_hotels')) {
            Schema::table('booking_hotels', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_hotels', 'currency')) {
                    $table->string('currency', 20)->default('SAR')->after('supplier_id');
                }
                if (!Schema::hasColumn('booking_hotels', 'cost_roe')) {
                    $table->decimal('cost_roe', 12, 4)->default(75.0000)->after('currency');
                }
                if (!Schema::hasColumn('booking_hotels', 'sale_roe')) {
                    $table->decimal('sale_roe', 12, 4)->default(75.0000)->after('cost_roe');
                }
            });
        }

        // 3. Booking Transports Table
        if (Schema::hasTable('booking_transports')) {
            Schema::table('booking_transports', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_transports', 'currency')) {
                    $table->string('currency', 20)->default('SAR')->after('supplier_id');
                }
                if (!Schema::hasColumn('booking_transports', 'cost_roe')) {
                    $table->decimal('cost_roe', 12, 4)->default(75.0000)->after('currency');
                }
                if (!Schema::hasColumn('booking_transports', 'sale_roe')) {
                    $table->decimal('sale_roe', 12, 4)->default(75.0000)->after('cost_roe');
                }
            });
        }

        // 4. Booking Visas Table
        if (Schema::hasTable('booking_visas')) {
            Schema::table('booking_visas', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_visas', 'currency')) {
                    $table->string('currency', 20)->default('SAR')->after('supplier_id');
                }
                if (!Schema::hasColumn('booking_visas', 'cost_roe')) {
                    $table->decimal('cost_roe', 12, 4)->default(75.0000)->after('currency');
                }
                if (!Schema::hasColumn('booking_visas', 'sale_roe')) {
                    $table->decimal('sale_roe', 12, 4)->default(75.0000)->after('cost_roe');
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
                foreach (['flight_currency', 'flight_cost_roe', 'flight_sale_roe'] as $col) {
                    if (Schema::hasColumn('bookings', $col)) $table->dropColumn($col);
                }
            });
        }
        if (Schema::hasTable('booking_hotels')) {
            Schema::table('booking_hotels', function (Blueprint $table) {
                foreach (['currency', 'cost_roe', 'sale_roe'] as $col) {
                    if (Schema::hasColumn('booking_hotels', $col)) $table->dropColumn($col);
                }
            });
        }
        if (Schema::hasTable('booking_transports')) {
            Schema::table('booking_transports', function (Blueprint $table) {
                foreach (['currency', 'cost_roe', 'sale_roe'] as $col) {
                    if (Schema::hasColumn('booking_transports', $col)) $table->dropColumn($col);
                }
            });
        }
        if (Schema::hasTable('booking_visas')) {
            Schema::table('booking_visas', function (Blueprint $table) {
                foreach (['currency', 'cost_roe', 'sale_roe'] as $col) {
                    if (Schema::hasColumn('booking_visas', $col)) $table->dropColumn($col);
                }
            });
        }
    }
};
