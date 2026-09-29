<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_room_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade');
            $table->string('batch_name')->nullable();
            $table->string('room_type')->default('Double');
            $table->string('room_view')->default('City View')->nullable();
            $table->string('meal_plan')->default('Room Only')->nullable();
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->integer('total_rooms')->default(0);
            $table->decimal('cost_rate', 12, 2)->default(0)->nullable();
            $table->decimal('selling_rate', 12, 2)->default(0)->nullable();
            $table->string('currency')->default('SAR');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_room_inventories');
    }
};