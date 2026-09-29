<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->string('subject');
            $table->dateTime('create_date')->nullable();
            $table->enum('status', [
                'pending',
                'done',
                'need further follow up'
            ])->default('pending');

            $table->text('remarks')->nullable();

            $table->dateTime('taken_at')->nullable();

            $table->dateTime('due_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('lead_id');
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
