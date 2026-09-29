<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('booking_visas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->string('passport_number')->nullable();
            $table->string('given_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('company')->nullable();
            $table->enum('send_to', ['shirka', 'consulate', 'both'])->nullable();
            $table->enum('status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_visas');
    }
};
