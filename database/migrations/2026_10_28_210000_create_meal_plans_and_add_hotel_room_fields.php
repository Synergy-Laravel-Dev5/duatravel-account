<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create meal_plans table
        if (!Schema::hasTable('meal_plans')) {
            Schema::create('meal_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });

            // Seed common meal plans
            $defaults = [
                ['name' => 'Room Only', 'code' => 'RO', 'description' => 'Accommodation only without meals', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Bed & Breakfast', 'code' => 'BB', 'description' => 'Daily buffet breakfast included', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Half Board', 'code' => 'HB', 'description' => 'Breakfast and dinner included', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Full Board', 'code' => 'FB', 'description' => 'Breakfast, lunch, and dinner included', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Sahoor Only', 'code' => 'SO', 'description' => 'Ramadan Sahoor meal included', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Iftar Only', 'code' => 'IO', 'description' => 'Ramadan Iftar buffet included', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Sahoor & Iftar', 'code' => 'SI', 'description' => 'Ramadan Sahoor and Iftar meals included', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ];
            DB::table('meal_plans')->insert($defaults);
        }

        // 2. Add meal_plan and room_view to booking_hotels
        Schema::table('booking_hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_hotels', 'meal_plan')) {
                $table->string('meal_plan')->nullable()->after('room_number');
            }
            if (!Schema::hasColumn('booking_hotels', 'room_view')) {
                $table->string('room_view')->nullable()->after('meal_plan');
            }
        });

        // 3. Add booking_category to bookings
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'booking_category')) {
                $table->string('booking_category')->default('umrah')->after('package_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'booking_category')) {
                $table->dropColumn('booking_category');
            }
        });

        Schema::table('booking_hotels', function (Blueprint $table) {
            if (Schema::hasColumn('booking_hotels', 'room_view')) {
                $table->dropColumn('room_view');
            }
            if (Schema::hasColumn('booking_hotels', 'meal_plan')) {
                $table->dropColumn('meal_plan');
            }
        });

        Schema::dropIfExists('meal_plans');
    }
};
