<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('travel_groups')) {
            Schema::create('travel_groups', function (Blueprint $table) {
                $table->id();
                $table->enum('group_type', ['arrival', 'departure']);
                $table->string('group_name');
                $table->foreignId('airline_id')->nullable()->constrained('airlines')->onDelete('set null');
                $table->foreignId('flight_id')->nullable()->constrained('flights')->onDelete('set null');
                $table->string('flight_number')->nullable();
                $table->date('flight_date')->nullable();
                $table->string('flight_time')->nullable();
                $table->string('pnr')->nullable();
                $table->string('departure_city')->nullable();
                $table->string('arrival_city')->nullable();
                $table->string('status')->default('active');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('travel_group_persons')) {
            Schema::create('travel_group_persons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('travel_group_id')->constrained('travel_groups')->onDelete('cascade');
                $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
                $table->foreignId('booking_person_id')->constrained('booking_persons')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_group_persons');
        Schema::dropIfExists('travel_groups');
    }
};
