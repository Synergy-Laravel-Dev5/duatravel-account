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
        Schema::create('airlines', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable()->unique();
            $table->string('iata_code')->nullable();
            $table->string('icao_code')->nullable();
            $table->string('country')->nullable();
            $table->string('call_sign')->nullable();
            $table->string('logo')->nullable();
            $table->string('ffnumber')->nullable();
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
        Schema::dropIfExists('airlines');
    }
};
