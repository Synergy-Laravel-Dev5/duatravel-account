<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. hotel_room_inventories: male_beds, female_beds, total_beds
        Schema::table('hotel_room_inventories', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_room_inventories', 'male_beds')) {
                $table->integer('male_beds')->default(0)->nullable()->after('total_rooms');
            }
            if (!Schema::hasColumn('hotel_room_inventories', 'female_beds')) {
                $table->integer('female_beds')->default(0)->nullable()->after('male_beds');
            }
            if (!Schema::hasColumn('hotel_room_inventories', 'total_beds')) {
                $table->integer('total_beds')->default(0)->nullable()->after('female_beds');
            }
        });

        // 2. quotation_accommodations: separate ROE and currencies, sharing male/female beds
        Schema::table('quotation_accommodations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotation_accommodations', 'cost_currency')) {
                $table->string('cost_currency')->default('PKR')->after('currency');
            }
            if (!Schema::hasColumn('quotation_accommodations', 'cost_exchange_rate')) {
                $table->decimal('cost_exchange_rate', 10, 4)->default(1)->after('exchange_rate');
            }
            if (!Schema::hasColumn('quotation_accommodations', 'selling_currency')) {
                $table->string('selling_currency')->default('PKR')->after('cost_exchange_rate');
            }
            if (!Schema::hasColumn('quotation_accommodations', 'selling_exchange_rate')) {
                $table->decimal('selling_exchange_rate', 10, 4)->default(1)->after('selling_currency');
            }
            if (!Schema::hasColumn('quotation_accommodations', 'no_of_beds')) {
                $table->integer('no_of_beds')->default(0)->after('no_of_rooms');
            }
            if (!Schema::hasColumn('quotation_accommodations', 'male_beds')) {
                $table->integer('male_beds')->default(0)->after('no_of_beds');
            }
            if (!Schema::hasColumn('quotation_accommodations', 'female_beds')) {
                $table->integer('female_beds')->default(0)->after('male_beds');
            }
        });

        // 3. quotations: total_supplier_amount and PSF fields
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'total_supplier_amount')) {
                $table->decimal('total_supplier_amount', 15, 2)->default(0)->after('total_profit_pkr');
            }
            if (!Schema::hasColumn('quotations', 'psf_description')) {
                $table->string('psf_description')->nullable()->after('total_supplier_amount');
            }
            if (!Schema::hasColumn('quotations', 'psf_quantity')) {
                $table->integer('psf_quantity')->default(1)->after('psf_description');
            }
            if (!Schema::hasColumn('quotations', 'psf_cost_currency')) {
                $table->string('psf_cost_currency')->default('PKR')->after('psf_quantity');
            }
            if (!Schema::hasColumn('quotations', 'psf_cost_roe')) {
                $table->decimal('psf_cost_roe', 10, 4)->default(1)->after('psf_cost_currency');
            }
            if (!Schema::hasColumn('quotations', 'psf_cost_amount')) {
                $table->decimal('psf_cost_amount', 12, 2)->default(0)->after('psf_cost_roe');
            }
            if (!Schema::hasColumn('quotations', 'psf_cost_pkr')) {
                $table->decimal('psf_cost_pkr', 15, 2)->default(0)->after('psf_cost_amount');
            }
            if (!Schema::hasColumn('quotations', 'psf_selling_currency')) {
                $table->string('psf_selling_currency')->default('PKR')->after('psf_cost_pkr');
            }
            if (!Schema::hasColumn('quotations', 'psf_selling_roe')) {
                $table->decimal('psf_selling_roe', 10, 4)->default(1)->after('psf_selling_currency');
            }
            if (!Schema::hasColumn('quotations', 'psf_selling_amount')) {
                $table->decimal('psf_selling_amount', 12, 2)->default(0)->after('psf_selling_roe');
            }
            if (!Schema::hasColumn('quotations', 'psf_selling_pkr')) {
                $table->decimal('psf_selling_pkr', 15, 2)->default(0)->after('psf_selling_amount');
            }
            if (!Schema::hasColumn('quotations', 'psf_supplier_amount')) {
                $table->decimal('psf_supplier_amount', 12, 2)->default(0)->after('psf_selling_pkr');
            }
            if (!Schema::hasColumn('quotations', 'psf_supplier_id')) {
                $table->unsignedBigInteger('psf_supplier_id')->nullable()->after('psf_supplier_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hotel_room_inventories', function (Blueprint $table) {
            $table->dropColumn(['male_beds', 'female_beds', 'total_beds']);
        });

        Schema::table('quotation_accommodations', function (Blueprint $table) {
            $table->dropColumn([
                'cost_currency',
                'cost_exchange_rate',
                'selling_currency',
                'selling_exchange_rate',
                'no_of_beds',
                'male_beds',
                'female_beds',
            ]);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'total_supplier_amount',
                'psf_description',
                'psf_quantity',
                'psf_cost_currency',
                'psf_cost_roe',
                'psf_cost_amount',
                'psf_cost_pkr',
                'psf_selling_currency',
                'psf_selling_roe',
                'psf_selling_amount',
                'psf_selling_pkr',
                'psf_supplier_amount',
                'psf_supplier_id',
            ]);
        });
    }
};
