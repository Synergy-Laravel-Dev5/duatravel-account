<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('expense_transactions')) {
            Schema::table('expense_transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('expense_transactions', 'bank_id')) {
                    $table->unsignedBigInteger('bank_id')->nullable()->after('account_id');
                }
                if (!Schema::hasColumn('expense_transactions', 'supplier_id')) {
                    $table->unsignedBigInteger('supplier_id')->nullable()->after('staff_id');
                }
                if (!Schema::hasColumn('expense_transactions', 'client_id')) {
                    $table->unsignedBigInteger('client_id')->nullable()->after('supplier_id');
                }
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'flight_cost_rate')) {
                    $table->decimal('flight_cost_rate', 15, 2)->default(0)->after('flight_charges');
                }
                if (!Schema::hasColumn('bookings', 'flight_cost')) {
                    $table->decimal('flight_cost', 15, 2)->default(0)->after('flight_cost_rate');
                }
                if (!Schema::hasColumn('bookings', 'flight_selling_rate')) {
                    $table->decimal('flight_selling_rate', 15, 2)->default(0)->after('flight_cost');
                }
                if (!Schema::hasColumn('bookings', 'transport_charges')) {
                    $table->decimal('transport_charges', 15, 2)->default(0)->after('flight_charges');
                }
                if (!Schema::hasColumn('bookings', 'hotel_charges')) {
                    $table->decimal('hotel_charges', 15, 2)->default(0)->after('transport_charges');
                }
            });
        }

        if (Schema::hasTable('booking_transports')) {
            Schema::table('booking_transports', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_transports', 'supplier_id')) {
                    $table->unsignedBigInteger('supplier_id')->nullable()->after('booking_id');
                }
                if (!Schema::hasColumn('booking_transports', 'cost_rate')) {
                    $table->decimal('cost_rate', 15, 2)->default(0)->after('transport_type');
                }
                if (!Schema::hasColumn('booking_transports', 'cost_amount')) {
                    $table->decimal('cost_amount', 15, 2)->default(0)->after('cost_rate');
                }
                if (!Schema::hasColumn('booking_transports', 'selling_rate')) {
                    $table->decimal('selling_rate', 15, 2)->default(0)->after('cost_amount');
                }
                if (!Schema::hasColumn('booking_transports', 'selling_amount')) {
                    $table->decimal('selling_amount', 15, 2)->default(0)->after('selling_rate');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('expense_transactions')) {
            Schema::table('expense_transactions', function (Blueprint $table) {
                $columns = ['bank_id', 'supplier_id', 'client_id'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('expense_transactions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                $columns = ['flight_cost_rate', 'flight_cost', 'flight_selling_rate', 'transport_charges', 'hotel_charges'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('bookings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('booking_transports')) {
            Schema::table('booking_transports', function (Blueprint $table) {
                $columns = ['supplier_id', 'cost_rate', 'cost_amount', 'selling_rate', 'selling_amount'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('booking_transports', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
