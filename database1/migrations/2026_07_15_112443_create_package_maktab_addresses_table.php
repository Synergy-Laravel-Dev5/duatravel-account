<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_maktab_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->unique()->constrained('packages')->onDelete('cascade');
            $table->string('maktab_address')->nullable();
            $table->string('office_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_maktab_addresses');
    }
};
