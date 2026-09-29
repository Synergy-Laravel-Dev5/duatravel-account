<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giveaways', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();   // e.g. GW-01
            $table->string('name');             // e.g. Suit Case
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('package_giveaway');
        Schema::dropIfExists('giveaways');
    }
};
