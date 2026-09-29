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
        Schema::create('booking_hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->enum('location', ['makkah', 'madinah', 'other'])->default('makkah');
            $table->string('hotel_name');
            $table->integer('no_of_nights')->default(1);
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->enum('room_type', ['single', 'double', 'triple', 'quad', 'suite']);
            $table->integer('no_of_rooms')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_hotels');
    }
};
