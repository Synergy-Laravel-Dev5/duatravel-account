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
            $table->enum('currency', ['PKR', 'SAR'])
                ->default('PKR')
                ->after('payment_type');

            $table->decimal('exchange_rate', 10, 2)
                ->default(1.00)
                ->after('currency');
        });
    }
    public function down(): void
    {
        Schema::table('expense_transactions', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate']);
        });
    }
};
