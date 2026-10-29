<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Clients Opening Balances
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'opening_balance_pkr')) {
                $table->decimal('opening_balance_pkr', 15, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('clients', 'opening_balance_sar')) {
                $table->decimal('opening_balance_sar', 15, 2)->default(0)->after('opening_balance_pkr');
            }
            if (!Schema::hasColumn('clients', 'opening_balance_pkr_type')) {
                $table->enum('opening_balance_pkr_type', ['debit', 'credit'])->default('debit')->after('opening_balance_sar');
            }
            if (!Schema::hasColumn('clients', 'opening_balance_sar_type')) {
                $table->enum('opening_balance_sar_type', ['debit', 'credit'])->default('debit')->after('opening_balance_pkr_type');
            }
        });

        // 2. Suppliers Opening Balances
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'opening_balance_pkr')) {
                $table->decimal('opening_balance_pkr', 15, 2)->default(0)->after('opening_balance');
            }
            if (!Schema::hasColumn('suppliers', 'opening_balance_sar')) {
                $table->decimal('opening_balance_sar', 15, 2)->default(0)->after('opening_balance_pkr');
            }
            if (!Schema::hasColumn('suppliers', 'opening_balance_pkr_type')) {
                $table->enum('opening_balance_pkr_type', ['debit', 'credit'])->default('credit')->after('opening_balance_sar');
            }
            if (!Schema::hasColumn('suppliers', 'opening_balance_sar_type')) {
                $table->enum('opening_balance_sar_type', ['debit', 'credit'])->default('credit')->after('opening_balance_pkr_type');
            }
        });

        // 3. Banks Opening Balances & Type
        Schema::table('banks', function (Blueprint $table) {
            if (!Schema::hasColumn('banks', 'account_type')) {
                $table->enum('account_type', ['bank', 'cash'])->default('bank')->after('branch');
            }
            if (!Schema::hasColumn('banks', 'opening_balance_pkr')) {
                $table->decimal('opening_balance_pkr', 15, 2)->default(0)->after('account_type');
            }
            if (!Schema::hasColumn('banks', 'opening_balance_sar')) {
                $table->decimal('opening_balance_sar', 15, 2)->default(0)->after('opening_balance_pkr');
            }
        });

        // 4. Accounts Opening Balances & Type
        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('accounts', 'account_type')) {
                    $table->enum('account_type', ['cash', 'bank', 'general'])->default('cash')->after('account_name');
                }
                if (!Schema::hasColumn('accounts', 'opening_balance_pkr')) {
                    $table->decimal('opening_balance_pkr', 15, 2)->default(0)->after('account_type');
                }
                if (!Schema::hasColumn('accounts', 'opening_balance_sar')) {
                    $table->decimal('opening_balance_sar', 15, 2)->default(0)->after('opening_balance_pkr');
                }
            });
        }

        // 5. Transactions Currency, ROE & SAR Amount
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'currency')) {
                $table->string('currency', 10)->default('PKR')->after('amount');
            }
            if (!Schema::hasColumn('transactions', 'exchange_rate')) {
                $table->decimal('exchange_rate', 10, 4)->default(1.0000)->after('currency');
            }
            if (!Schema::hasColumn('transactions', 'sar_amount')) {
                $table->decimal('sar_amount', 15, 2)->default(0)->after('exchange_rate');
            }
        });

        // 6. Bookings Tab Vouchers & Flight Time Format
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'flight_voucher_no')) {
                $table->string('flight_voucher_no', 100)->nullable()->after('voucher_number');
            }
            if (!Schema::hasColumn('bookings', 'hotel_voucher_no')) {
                $table->string('hotel_voucher_no', 100)->nullable()->after('flight_voucher_no');
            }
            if (!Schema::hasColumn('bookings', 'transport_voucher_no')) {
                $table->string('transport_voucher_no', 100)->nullable()->after('hotel_voucher_no');
            }
            if (!Schema::hasColumn('bookings', 'visa_voucher_no')) {
                $table->string('visa_voucher_no', 100)->nullable()->after('transport_voucher_no');
            }
            if (!Schema::hasColumn('bookings', 'flight_time_format')) {
                $table->string('flight_time_format', 10)->default('24')->after('departure_time');
            }
        });

        // 7. Booking Hotels Voucher & Additional Rooms
        Schema::table('booking_hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_hotels', 'voucher_no')) {
                $table->string('voucher_no', 100)->nullable()->after('hotel_voucher');
            }
            if (!Schema::hasColumn('booking_hotels', 'additional_rooms')) {
                $table->longText('additional_rooms')->nullable()->after('voucher_no');
            }
        });

        // 8. Booking Transports Voucher
        Schema::table('booking_transports', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_transports', 'voucher_no')) {
                $table->string('voucher_no', 100)->nullable()->after('transport_ticket');
            }
        });

        // 9. Booking Visas Voucher
        Schema::table('booking_visas', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_visas', 'voucher_no')) {
                $table->string('voucher_no', 100)->nullable()->after('visa_attachment');
            }
        });

        // 10. Booking Persons Multi-Supplier & Individual Flight Info
        Schema::table('booking_persons', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_persons', 'flight_supplier_id')) {
                $table->unsignedBigInteger('flight_supplier_id')->nullable()->after('flight_sell');
            }
            if (!Schema::hasColumn('booking_persons', 'flight_pnr')) {
                $table->string('flight_pnr', 100)->nullable()->after('flight_supplier_id');
            }
            if (!Schema::hasColumn('booking_persons', 'flight_date')) {
                $table->date('flight_date')->nullable()->after('flight_pnr');
            }
            if (!Schema::hasColumn('booking_persons', 'flight_time')) {
                $table->string('flight_time', 50)->nullable()->after('flight_date');
            }
        });
    }

    public function down(): void
    {
        // Safe reversible rollback
    }
};
