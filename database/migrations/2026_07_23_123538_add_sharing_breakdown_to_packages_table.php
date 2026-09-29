<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->json('makkah_a')->nullable()->after('mina_type');
            $table->json('makkah_b')->nullable()->after('makkah_a');
            $table->json('madinah_a')->nullable()->after('makkah_b');
            $table->json('madinah_b')->nullable()->after('madinah_a');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['makkah_a', 'makkah_b', 'madinah_a', 'madinah_b']);
        });
    }
};