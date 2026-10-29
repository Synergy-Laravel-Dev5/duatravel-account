<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                if (!Schema::hasColumn('clients', 'opening_balance_roe')) {
                    $table->decimal('opening_balance_roe', 10, 4)->nullable()->default(75.0000)->after('opening_balance_sar_type');
                }
            });
        }

        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $table) {
                if (!Schema::hasColumn('suppliers', 'opening_balance_roe')) {
                    $table->decimal('opening_balance_roe', 10, 4)->nullable()->default(75.0000)->after('opening_balance_sar_type');
                }
            });
        }

        if (Schema::hasTable('banks')) {
            Schema::table('banks', function (Blueprint $table) {
                if (!Schema::hasColumn('banks', 'opening_balance_roe')) {
                    $table->decimal('opening_balance_roe', 10, 4)->nullable()->default(75.0000)->after('opening_balance_sar');
                }
            });
        }

        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('accounts', 'opening_balance_roe')) {
                    $table->decimal('opening_balance_roe', 10, 4)->nullable()->default(75.0000)->after('opening_balance_sar');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                if (Schema::hasColumn('clients', 'opening_balance_roe')) {
                    $table->dropColumn('opening_balance_roe');
                }
            });
        }

        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $table) {
                if (Schema::hasColumn('suppliers', 'opening_balance_roe')) {
                    $table->dropColumn('opening_balance_roe');
                }
            });
        }

        if (Schema::hasTable('banks')) {
            Schema::table('banks', function (Blueprint $table) {
                if (Schema::hasColumn('banks', 'opening_balance_roe')) {
                    $table->dropColumn('opening_balance_roe');
                }
            });
        }

        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                if (Schema::hasColumn('accounts', 'opening_balance_roe')) {
                    $table->dropColumn('opening_balance_roe');
                }
            });
        }
    }
};
