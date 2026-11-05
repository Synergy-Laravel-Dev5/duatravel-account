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
            if (!Schema::hasColumn('bookings', 'return_flight_supplier_id')) {
                $table->unsignedBigInteger('return_flight_supplier_id')->nullable()->after('flight_supplier_id');
            }
            if (!Schema::hasColumn('bookings', 'additional_services_cost')) {
                $table->decimal('additional_services_cost', 15, 2)->default(0.00)->after('additional_services_amount');
            }
        });

        Schema::table('booking_persons', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_persons', 'return_flight_supplier_id')) {
                $table->unsignedBigInteger('return_flight_supplier_id')->nullable()->after('flight_supplier_id');
            }
        });

        Schema::table('expense_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('expense_transactions', 'booking_id')) {
                $table->unsignedBigInteger('booking_id')->nullable()->after('supplier_id');
            }
            if (!Schema::hasColumn('expense_transactions', 'service_type')) {
                $table->string('service_type', 50)->nullable()->after('booking_id');
            }
            if (!Schema::hasColumn('expense_transactions', 'refund_type')) {
                $table->string('refund_type', 50)->nullable()->after('service_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'return_flight_supplier_id')) {
                $table->dropColumn('return_flight_supplier_id');
            }
            if (Schema::hasColumn('bookings', 'additional_services_cost')) {
                $table->dropColumn('additional_services_cost');
            }
        });

        Schema::table('booking_persons', function (Blueprint $table) {
            if (Schema::hasColumn('booking_persons', 'return_flight_supplier_id')) {
                $table->dropColumn('return_flight_supplier_id');
            }
        });

        Schema::table('expense_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('expense_transactions', 'booking_id')) {
                $table->dropColumn('booking_id');
            }
            if (Schema::hasColumn('expense_transactions', 'service_type')) {
                $table->dropColumn('service_type');
            }
            if (Schema::hasColumn('expense_transactions', 'refund_type')) {
                $table->dropColumn('refund_type');
            }
        });
    }
};
