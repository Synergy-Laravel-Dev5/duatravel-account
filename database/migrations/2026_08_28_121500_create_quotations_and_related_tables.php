<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique();
            $table->string('quotation_title')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('client_email')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            
            $table->integer('total_pax')->default(1);
            $table->integer('adults_count')->default(1);
            $table->integer('children_count')->default(0);
            $table->integer('infants_count')->default(0);

            $table->decimal('total_cost_pkr', 15, 2)->default(0);
            $table->decimal('total_sale_pkr', 15, 2)->default(0);
            $table->decimal('total_profit_pkr', 15, 2)->default(0);
            $table->decimal('margin_percentage', 5, 2)->default(0);
            $table->decimal('per_pax_sale_pkr', 15, 2)->default(0);

            $table->text('inclusions')->nullable();
            $table->text('exclusions')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->text('remarks')->nullable();

            $table->string('status')->default('draft');
            $table->date('valid_until')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('quotation_accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->string('city')->default('Makkah');
            $table->string('hotel_name')->nullable();
            $table->unsignedBigInteger('hotel_id')->nullable();
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->integer('number_of_nights')->default(1);
            $table->string('room_type')->default('Double');
            $table->string('room_view')->default('City View');
            $table->string('confirmation_number')->nullable();
            $table->string('meal_plan')->default('Room Only');
            $table->integer('no_of_rooms')->default(1);
            $table->decimal('per_night_rate', 12, 2)->default(0);
            $table->boolean('add_total_rate')->default(false);
            $table->string('currency')->default('SAR');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->decimal('cost_amount', 12, 2)->default(0);
            $table->decimal('cost_amount_pkr', 15, 2)->default(0);
            $table->decimal('selling_amount', 12, 2)->default(0);
            $table->decimal('selling_amount_pkr', 15, 2)->default(0);
            $table->decimal('supplier_amount', 12, 2)->default(0);
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->date('cancellation_deadline')->nullable();
            $table->date('finalization_date')->nullable();
            $table->timestamps();
        });

        Schema::create('quotation_meal_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->unsignedBigInteger('quotation_accommodation_id')->nullable();
            $table->string('meal_type')->default('Dinner');
            $table->string('city')->default('Makkah');
            $table->string('provider_name')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->integer('pax')->default(1);
            $table->integer('days')->default(1);
            $table->string('currency')->default('SAR');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->decimal('cost_rate', 12, 2)->default(0);
            $table->decimal('total_cost_pkr', 15, 2)->default(0);
            $table->decimal('selling_rate', 12, 2)->default(0);
            $table->decimal('total_sale_pkr', 15, 2)->default(0);
            $table->string('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('quotation_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->string('sector')->nullable();
            $table->string('vehicle_type')->default('Sedan Car');
            $table->string('transfer_date')->nullable();
            $table->integer('quantity')->default(1);
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('currency')->default('SAR');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->decimal('cost_amount', 12, 2)->default(0);
            $table->decimal('cost_amount_pkr', 15, 2)->default(0);
            $table->decimal('selling_amount', 12, 2)->default(0);
            $table->decimal('selling_amount_pkr', 15, 2)->default(0);
            $table->string('confirmation_number')->nullable();
            $table->timestamps();
        });

        Schema::create('quotation_tours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->string('tour_name')->nullable();
            $table->string('city')->default('Makkah');
            $table->string('sector')->nullable();
            $table->string('tour_date')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->integer('quantity')->default(1);
            $table->boolean('guide_included')->default(true);
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('currency')->default('SAR');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->decimal('cost_amount', 12, 2)->default(0);
            $table->decimal('cost_amount_pkr', 15, 2)->default(0);
            $table->decimal('selling_amount', 12, 2)->default(0);
            $table->decimal('selling_amount_pkr', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('quotation_flights_trains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->string('service_type')->default('Haramain High Speed Train');
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->string('travel_date')->nullable();
            $table->string('class_type')->default('Economy');
            $table->integer('pax_count')->default(1);
            $table->string('ticket_number_pnr')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('currency')->default('SAR');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->decimal('cost_amount', 12, 2)->default(0);
            $table->decimal('cost_amount_pkr', 15, 2)->default(0);
            $table->decimal('selling_amount', 12, 2)->default(0);
            $table->decimal('selling_amount_pkr', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('quotation_visas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->string('passenger_name')->nullable();
            $table->string('passenger_type')->default('Adult');
            $table->string('gender')->default('Male');
            $table->string('passport_number')->nullable();
            $table->string('visa_type')->default('Umrah Tourist E-Visa');
            $table->string('country')->default('Saudi Arabia');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('currency')->default('SAR');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->decimal('cost_amount', 12, 2)->default(0);
            $table->decimal('cost_amount_pkr', 15, 2)->default(0);
            $table->decimal('selling_amount', 12, 2)->default(0);
            $table->decimal('selling_amount_pkr', 15, 2)->default(0);
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_visas');
        Schema::dropIfExists('quotation_flights_trains');
        Schema::dropIfExists('quotation_tours');
        Schema::dropIfExists('quotation_transfers');
        Schema::dropIfExists('quotation_meal_plans');
        Schema::dropIfExists('quotation_accommodations');
        Schema::dropIfExists('quotations');
    }
};
