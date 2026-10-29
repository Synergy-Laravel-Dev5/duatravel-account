<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'entity_type')) {
                $table->string('entity_type', 50)->nullable()->after('account_type');
            }
            if (!Schema::hasColumn('accounts', 'entity_id')) {
                $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
            }
            if (!Schema::hasColumn('accounts', 'notes')) {
                $table->text('notes')->nullable()->after('opening_balance_roe');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'entity_type')) {
                $table->dropColumn('entity_type');
            }
            if (Schema::hasColumn('accounts', 'entity_id')) {
                $table->dropColumn('entity_id');
            }
            if (Schema::hasColumn('accounts', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
