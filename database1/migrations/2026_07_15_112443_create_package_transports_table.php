<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_transports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('route')->nullable();
            $table->string('arrival')->nullable();
            $table->string('departure')->nullable();
            $table->string('type')->nullable();
            $table->string('vehicle')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('package_transports', function (Blueprint $table) {
            $table->longText('details')->nullable();

            $table->dropColumn(['route', 'arrival', 'departure', 'type', 'vehicle']);
        });
    }
};
