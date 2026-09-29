<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_accommodations', function (Blueprint $table) {
            $table->date('azizia_date')->nullable()->after('check_out');
        });
    }

    public function down(): void
    {
        Schema::table('package_accommodations', function (Blueprint $table) {
            $table->dropColumn('azizia_date');
        });
    }
};
