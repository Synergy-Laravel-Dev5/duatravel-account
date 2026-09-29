<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            
            $table->string('place')->nullable();
            $table->string('accommodation_type')->nullable();
            $table->string('saudi_star_rating')->nullable();
            // $table->string('hotel')->nullable();
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->onDelete('cascade');
            
            $table->integer('distance')->nullable();
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->string('food_package')->nullable();

            $table->dateTime('actual_check_in_time')->nullable();
            $table->dateTime('actual_check_out_time')->nullable();

            $table->integer('days')->nullable();
            $table->integer('nights')->nullable();

            $table->enum('makkah_ziarat', ['yes', 'no'])->nullable();
            $table->enum('madinah_ziarat', ['yes', 'no'])->nullable();

            $table->string('group_ziarat')->nullable();
            $table->string('religious_lectures')->nullable();

            $table->string('distribution')->nullable();
            $table->string('camp')->nullable();
            $table->string('arafat')->nullable();

            $table->string('shuttle')->nullable();
            $table->string('bedding')->nullable();
            $table->string('sharing')->nullable();
            $table->string('sharing_type')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_accommodations');
    }
};
