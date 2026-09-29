<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->nullable()->constrained('airlines')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('outbound_flight_no')->nullable();
            $table->dateTime('outbound_departure')->nullable();
            $table->dateTime('outbound_arrival')->nullable();
            $table->string('inbound_flight_no')->nullable();
            $table->dateTime('inbound_departure')->nullable();
            $table->dateTime('inbound_arrival')->nullable();
            $table->integer('economy_seats')->nullable();
            $table->integer('business_seats')->nullable();
            $table->enum('status', ['active', 'inactive'])->nullable()->default('active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('flights');
    }
};
