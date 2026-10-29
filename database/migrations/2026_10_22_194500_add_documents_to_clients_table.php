<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'cnic_document')) {
                $table->string('cnic_document')->nullable()->after('cnic');
            }
            if (!Schema::hasColumn('clients', 'passport_document')) {
                $table->string('passport_document')->nullable()->after('passport_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'cnic_document')) {
                $table->dropColumn('cnic_document');
            }
            if (Schema::hasColumn('clients', 'passport_document')) {
                $table->dropColumn('passport_document');
            }
        });
    }
};
