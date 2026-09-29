<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->unique()->constrained('packages')->onDelete('cascade');

            $table->longText('description')->nullable();

            $table->string('mina_image')->nullable();
            $table->string('arafat_image')->nullable();
            $table->string('muzdalifah_image')->nullable();
            $table->string('makkah_mina_rami_day_one_image')->nullable();
            $table->string('mina_rami_day_two_image')->nullable();
            $table->string('mina_makkah_rami_day_three_image')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_itineraries');
    }
};