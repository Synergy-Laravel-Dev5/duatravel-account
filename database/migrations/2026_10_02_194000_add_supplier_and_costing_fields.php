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
        if (Schema::hasTable('quotation_accommodations')) {
            Schema::table('quotation_accommodations', function (Blueprint $table) {
                if (!Schema::hasColumn('quotation_accommodations', 'selling_per_night_rate')) {
                    $table->decimal('selling_per_night_rate', 15, 2)->nullable()->default(0)->after('per_night_rate');
                }
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'flight_supplier_id')) {
                    $table->unsignedBigInteger('flight_supplier_id')->nullable()->after('arrival_pnr');
                }
            });
        }

        if (Schema::hasTable('booking_hotels')) {
            Schema::table('booking_hotels', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_hotels', 'supplier_id')) {
                    $table->unsignedBigInteger('supplier_id')->nullable()->after('hotel_name');
                }
                if (!Schema::hasColumn('booking_hotels', 'cost_per_night')) {
                    $table->decimal('cost_per_night', 15, 2)->nullable()->default(0)->after('no_of_rooms');
                }
                if (!Schema::hasColumn('booking_hotels', 'cost_amount')) {
                    $table->decimal('cost_amount', 15, 2)->nullable()->default(0)->after('cost_per_night');
                }
                if (!Schema::hasColumn('booking_hotels', 'selling_per_night')) {
                    $table->decimal('selling_per_night', 15, 2)->nullable()->default(0)->after('cost_amount');
                }
                if (!Schema::hasColumn('booking_hotels', 'selling_amount')) {
                    $table->decimal('selling_amount', 15, 2)->nullable()->default(0)->after('selling_per_night');
                }
            });
        }

        if (Schema::hasTable('booking_visas')) {
            Schema::table('booking_visas', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_visas', 'supplier_id')) {
                    $table->unsignedBigInteger('supplier_id')->nullable()->after('company');
                }
                if (!Schema::hasColumn('booking_visas', 'cost_rate')) {
                    $table->decimal('cost_rate', 15, 2)->nullable()->default(0)->after('status');
                }
                if (!Schema::hasColumn('booking_visas', 'cost_amount')) {
                    $table->decimal('cost_amount', 15, 2)->nullable()->default(0)->after('cost_rate');
                }
                if (!Schema::hasColumn('booking_visas', 'selling_rate')) {
                    $table->decimal('selling_rate', 15, 2)->nullable()->default(0)->after('cost_amount');
                }
                if (!Schema::hasColumn('booking_visas', 'selling_amount')) {
                    $table->decimal('selling_amount', 15, 2)->nullable()->default(0)->after('selling_rate');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('quotation_accommodations')) {
            Schema::table('quotation_accommodations', function (Blueprint $table) {
                if (Schema::hasColumn('quotation_accommodations', 'selling_per_night_rate')) {
                    $table->dropColumn('selling_per_night_rate');
                }
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (Schema::hasColumn('bookings', 'flight_supplier_id')) {
                    $table->dropColumn('flight_supplier_id');
                }
            });
        }

        if (Schema::hasTable('booking_hotels')) {
            Schema::table('booking_hotels', function (Blueprint $table) {
                $cols = ['supplier_id', 'cost_per_night', 'cost_amount', 'selling_per_night', 'selling_amount'];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('booking_hotels', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }

        if (Schema::hasTable('booking_visas')) {
            Schema::table('booking_visas', function (Blueprint $table) {
                $cols = ['supplier_id', 'cost_rate', 'cost_amount', 'selling_rate', 'selling_amount'];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('booking_visas', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }
    }
};
