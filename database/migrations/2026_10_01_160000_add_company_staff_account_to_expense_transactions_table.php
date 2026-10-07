<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expense_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('expense_transactions', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('expense_id')->constrained('accounts')->onDelete('set null');
            }
            if (!Schema::hasColumn('expense_transactions', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('account_id')->constrained('companies')->onDelete('set null');
            }
            if (!Schema::hasColumn('expense_transactions', 'staff_id')) {
                $table->foreignId('staff_id')->nullable()->after('company_id')->constrained('staff')->onDelete('set null');
            }
            if (!Schema::hasColumn('expense_transactions', 'transaction_type')) {
                $table->string('transaction_type')->default('credit')->after('payment_type');
            }
            if (!Schema::hasColumn('expense_transactions', 'payee_type')) {
                $table->string('payee_type')->default('company')->after('transaction_type');
            }
        });

        if (!Schema::hasTable('expense_transaction_companies')) {
            Schema::create('expense_transaction_companies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('expense_transaction_id')->constrained('expense_transactions')->onDelete('cascade');
                $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_transaction_companies');

        Schema::table('expense_transactions', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['staff_id']);
            $table->dropColumn(['account_id', 'company_id', 'staff_id', 'transaction_type', 'payee_type']);
        });
    }
};
