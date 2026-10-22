<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clients') && !Schema::hasColumn('clients', 'package_type')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('package_type')->default('hajj')->after('package_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'package_type')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('package_type');
            });
        }
    }
};
