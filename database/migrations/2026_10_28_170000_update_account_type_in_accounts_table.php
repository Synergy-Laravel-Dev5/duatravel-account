<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change enum to VARCHAR(50) so loan_asset, loan_liability, cash, bank, etc. can be stored
        DB::statement("ALTER TABLE `accounts` MODIFY `account_type` VARCHAR(50) NOT NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `accounts` MODIFY `account_type` ENUM('cash','bank','general') NOT NULL DEFAULT 'cash'");
    }
};
