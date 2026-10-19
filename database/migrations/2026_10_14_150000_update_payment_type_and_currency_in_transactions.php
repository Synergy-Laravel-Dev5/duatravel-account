<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change payment_type and currency on expense_transactions to varchar so all payment types (cheque, bank_transfer, etc.) and currencies (PKR, SAR, USD, AED, etc.) are accepted without truncation
        DB::statement("ALTER TABLE `expense_transactions` MODIFY `payment_type` VARCHAR(50) NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE `expense_transactions` MODIFY `currency` VARCHAR(10) NOT NULL DEFAULT 'PKR'");

        if (Schema::hasTable('transactions')) {
            DB::statement("ALTER TABLE `transactions` MODIFY `payment_type` VARCHAR(50) NOT NULL DEFAULT 'cash'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe fallback
    }
};
