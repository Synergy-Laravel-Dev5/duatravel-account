<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unique('cnic');
            $table->unique('passport_number');
            // $table->enum('package_type', ['hajj', 'umrah'])->nullable()->after('passport_number');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['clients_cnic_unique']);
            $table->dropUnique(['clients_passport_number_unique']);
            // $table->dropColumn('package_type');
        });
    }
};
