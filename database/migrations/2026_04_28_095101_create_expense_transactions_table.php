<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expense_id')
                ->constrained('expenses')
                ->onDelete('cascade');

            // New Fields Added
            $table->enum('hajj_umrah', ['hajj', 'umrah'])->default('umrah');   // Hajj or Umrah
            $table->year('year');                                               // Year (e.g. 2025)

            $table->decimal('amount', 12, 2);

            $table->enum('payment_type', ['cash', 'bank', 'online']);

            $table->string('reference_no')->nullable();

            $table->string('proof')->nullable();   // image/pdf proof

            $table->date('date');

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_transactions');
    }
};
