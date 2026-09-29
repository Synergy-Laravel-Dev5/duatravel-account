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
        Schema::table('quotations', function (Blueprint $table) {
            $table->integer('total_pax')->nullable()->default(1)->change();
            $table->integer('adults_count')->nullable()->default(1)->change();
            $table->integer('children_count')->nullable()->default(0)->change();
            $table->integer('infants_count')->nullable()->default(0)->change();
            $table->decimal('total_cost_pkr', 15, 2)->nullable()->default(0)->change();
            $table->decimal('total_sale_pkr', 15, 2)->nullable()->default(0)->change();
            $table->decimal('total_profit_pkr', 15, 2)->nullable()->default(0)->change();
            $table->decimal('margin_percentage', 5, 2)->nullable()->default(0)->change();
            $table->decimal('per_pax_sale_pkr', 15, 2)->nullable()->default(0)->change();
            $table->string('status')->nullable()->default('draft')->change();
        });

        Schema::table('quotation_accommodations', function (Blueprint $table) {
            $table->string('city')->nullable()->change();
            $table->integer('number_of_nights')->nullable()->change();
            $table->string('room_type')->nullable()->change();
            $table->string('room_view')->nullable()->change();
            $table->string('meal_plan')->nullable()->change();
            $table->integer('no_of_rooms')->nullable()->change();
            $table->decimal('per_night_rate', 12, 2)->nullable()->default(0)->change();
            $table->boolean('add_total_rate')->nullable()->default(false)->change();
            $table->string('currency')->nullable()->default('SAR')->change();
            $table->decimal('exchange_rate', 10, 4)->nullable()->default(1)->change();
            $table->decimal('cost_amount', 12, 2)->nullable()->default(0)->change();
            $table->decimal('cost_amount_pkr', 15, 2)->nullable()->default(0)->change();
            $table->decimal('selling_amount', 12, 2)->nullable()->default(0)->change();
            $table->decimal('selling_amount_pkr', 15, 2)->nullable()->default(0)->change();
            $table->decimal('supplier_amount', 12, 2)->nullable()->default(0)->change();
        });

        Schema::table('quotation_meal_plans', function (Blueprint $table) {
            $table->string('meal_type')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->integer('pax')->nullable()->change();
            $table->integer('days')->nullable()->change();
            $table->string('currency')->nullable()->default('SAR')->change();
            $table->decimal('exchange_rate', 10, 4)->nullable()->default(1)->change();
            $table->decimal('cost_rate', 12, 2)->nullable()->default(0)->change();
            $table->decimal('total_cost_pkr', 15, 2)->nullable()->default(0)->change();
            $table->decimal('selling_rate', 12, 2)->nullable()->default(0)->change();
            $table->decimal('total_sale_pkr', 15, 2)->nullable()->default(0)->change();
        });

        Schema::table('quotation_transfers', function (Blueprint $table) {
            $table->string('vehicle_type')->nullable()->change();
            $table->integer('quantity')->nullable()->change();
            $table->string('currency')->nullable()->default('SAR')->change();
            $table->decimal('exchange_rate', 10, 4)->nullable()->default(1)->change();
            $table->decimal('cost_amount', 12, 2)->nullable()->default(0)->change();
            $table->decimal('cost_amount_pkr', 15, 2)->nullable()->default(0)->change();
            $table->decimal('selling_amount', 12, 2)->nullable()->default(0)->change();
            $table->decimal('selling_amount_pkr', 15, 2)->nullable()->default(0)->change();
        });

        Schema::table('quotation_tours', function (Blueprint $table) {
            $table->string('tour_name')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->integer('quantity')->nullable()->change();
            $table->boolean('guide_included')->nullable()->default(true)->change();
            $table->string('currency')->nullable()->default('SAR')->change();
            $table->decimal('exchange_rate', 10, 4)->nullable()->default(1)->change();
            $table->decimal('cost_amount', 12, 2)->nullable()->default(0)->change();
            $table->decimal('cost_amount_pkr', 15, 2)->nullable()->default(0)->change();
            $table->decimal('selling_amount', 12, 2)->nullable()->default(0)->change();
            $table->decimal('selling_amount_pkr', 15, 2)->nullable()->default(0)->change();
        });

        Schema::table('quotation_flights_trains', function (Blueprint $table) {
            $table->string('service_type')->nullable()->change();
            $table->string('class_type')->nullable()->change();
            $table->integer('pax_count')->nullable()->change();
            $table->string('currency')->nullable()->default('SAR')->change();
            $table->decimal('exchange_rate', 10, 4)->nullable()->default(1)->change();
            $table->decimal('cost_amount', 12, 2)->nullable()->default(0)->change();
            $table->decimal('cost_amount_pkr', 15, 2)->nullable()->default(0)->change();
            $table->decimal('selling_amount', 12, 2)->nullable()->default(0)->change();
            $table->decimal('selling_amount_pkr', 15, 2)->nullable()->default(0)->change();
        });

        Schema::table('quotation_visas', function (Blueprint $table) {
            $table->string('passenger_type')->nullable()->change();
            $table->string('gender')->nullable()->change();
            $table->string('visa_type')->nullable()->change();
            $table->string('country')->nullable()->change();
            $table->string('currency')->nullable()->default('SAR')->change();
            $table->decimal('exchange_rate', 10, 4)->nullable()->default(1)->change();
            $table->decimal('cost_amount', 12, 2)->nullable()->default(0)->change();
            $table->decimal('cost_amount_pkr', 15, 2)->nullable()->default(0)->change();
            $table->decimal('selling_amount', 12, 2)->nullable()->default(0)->change();
            $table->decimal('selling_amount_pkr', 15, 2)->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
    }
};
