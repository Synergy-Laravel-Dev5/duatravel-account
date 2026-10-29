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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_number', 50)->unique();
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->string('salary_month', 20); // e.g. January, February, ...
            $table->unsignedSmallInteger('salary_year'); // e.g. 2026
            $table->date('payment_date');

            // Working Days Calculation
            $table->decimal('total_days', 6, 2)->default(30.00);
            $table->decimal('off_days', 6, 2)->default(0.00);
            $table->decimal('working_days', 6, 2)->default(30.00);

            // Salary & Earnings
            $table->decimal('basic_salary', 12, 2)->default(0.00);
            $table->decimal('per_day_salary', 12, 2)->default(0.00);
            $table->decimal('absent_deduction', 12, 2)->default(0.00);
            $table->decimal('earned_salary', 12, 2)->default(0.00);
            $table->decimal('allowance', 12, 2)->default(0.00);
            $table->decimal('bonus', 12, 2)->default(0.00);
            $table->decimal('gross_salary', 12, 2)->default(0.00);

            // Deductions (Advance & Other)
            $table->decimal('advance_salary', 12, 2)->default(0.00);
            $table->decimal('other_deduction', 12, 2)->default(0.00);
            $table->decimal('total_deductions', 12, 2)->default(0.00);

            // Net Payable Salary
            $table->decimal('net_salary', 12, 2)->default(0.00);

            // Payment Details
            $table->string('payment_method', 50)->default('cash'); // cash, bank, cheque, online
            $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->enum('status', ['paid', 'unpaid', 'draft'])->default('paid');
            $table->foreignId('expense_transaction_id')->nullable()->constrained('expense_transactions')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
