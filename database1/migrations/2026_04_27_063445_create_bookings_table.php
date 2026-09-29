<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->enum('package_type', ['umrah', 'hajj', 'other'])->default('umrah');

            // Booking Details
            $table->string('given_name')->nullable();
            $table->string('sur_name')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('cnic')->nullable();
            $table->integer('no_of_pax')->default(1);
            $table->string('care_of')->nullable();
            $table->string('voucher_number')->nullable();
            $table->string('card_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('emergency_phone')->nullable();

            // Flight
            $table->date('departure_date')->nullable();
            $table->string('departure_flight')->nullable();
            $table->time('departure_time')->nullable();
            $table->date('arrival_date')->nullable();
            $table->string('arrival_flight')->nullable();
            $table->time('arrival_time')->nullable();
            $table->string('pnr_ticket')->nullable();
            $table->string('airline')->nullable();

            // Visa
            $table->string('visa_passport')->nullable();
            $table->string('visa_given_name')->nullable();
            $table->string('visa_sur_name')->nullable();
            $table->date('visa_dob')->nullable();
            $table->string('visa_company')->nullable();
            $table->enum('visa_send_to', ['shirka', 'consulate', 'both'])->nullable();

            // Package & Costing
            $table->string('package_name')->nullable();
            $table->decimal('package_cost', 10, 2)->default(0);
            $table->decimal('visa_charges', 10, 2)->default(0);
            $table->decimal('flight_charges', 10, 2)->default(0);
            $table->decimal('other_charges', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('total_received', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);

            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};