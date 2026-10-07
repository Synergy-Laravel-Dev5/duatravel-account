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
        // 1. Room Types Table
        if (!Schema::hasTable('room_types')) {
            Schema::create('room_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150)->unique();
                $table->string('code', 100)->nullable();
                $table->integer('capacity')->default(2);
                $table->text('description')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 2. Hotel Room Capacities Table
        if (!Schema::hasTable('hotel_room_capacities')) {
            Schema::create('hotel_room_capacities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hotel_id')->nullable()->constrained('hotels')->nullOnDelete();
                $table->string('hotel_name', 191);
                $table->string('location', 100)->nullable();
                $table->string('room_number', 100);
                $table->string('room_type', 100)->nullable();
                $table->string('gender', 50)->nullable()->default('Any');
                $table->integer('bed_capacity')->default(2);
                $table->integer('extra_beds')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['hotel_name', 'room_number']);
            });
        }

        // 3. Add allocation columns to booking_persons
        if (Schema::hasTable('booking_persons')) {
            Schema::table('booking_persons', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_persons', 'hb_number')) {
                    $table->string('hb_number', 100)->nullable()->after('haji_id');
                }
                if (!Schema::hasColumn('booking_persons', 'room_type')) {
                    $table->string('room_type', 100)->nullable()->after('room_number');
                }
                if (!Schema::hasColumn('booking_persons', 'hotel_name')) {
                    $table->string('hotel_name', 191)->nullable()->after('room_type');
                }
                if (!Schema::hasColumn('booking_persons', 'location')) {
                    $table->string('location', 100)->nullable()->default('makkah')->after('hotel_name');
                }
                if (!Schema::hasColumn('booking_persons', 'room_gender')) {
                    $table->string('room_gender', 50)->nullable()->default('Any')->after('location');
                }
            });
        }

        // 4. Add gender to booking_hotels
        if (Schema::hasTable('booking_hotels')) {
            Schema::table('booking_hotels', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_hotels', 'gender')) {
                    $table->string('gender', 50)->nullable()->default('Any')->after('room_number');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_room_capacities');
        Schema::dropIfExists('room_types');

        if (Schema::hasTable('booking_persons')) {
            Schema::table('booking_persons', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('booking_persons', 'hb_number')) $cols[] = 'hb_number';
                if (Schema::hasColumn('booking_persons', 'room_type')) $cols[] = 'room_type';
                if (Schema::hasColumn('booking_persons', 'hotel_name')) $cols[] = 'hotel_name';
                if (Schema::hasColumn('booking_persons', 'location')) $cols[] = 'location';
                if (Schema::hasColumn('booking_persons', 'room_gender')) $cols[] = 'room_gender';
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('booking_hotels')) {
            Schema::table('booking_hotels', function (Blueprint $table) {
                if (Schema::hasColumn('booking_hotels', 'gender')) {
                    $table->dropColumn('gender');
                }
            });
        }
    }
};
