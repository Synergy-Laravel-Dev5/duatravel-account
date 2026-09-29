<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('package_id')->nullable()->after('booking_for')->constrained('packages')->onDelete('set null');
        });

        Schema::table('booking_persons', function (Blueprint $table) {
            $table->string('surname')->nullable()->after('full_name');
            $table->string('given_name')->nullable()->after('surname');
            $table->string('father_name')->nullable()->after('given_name');
            $table->date('dob')->nullable()->after('father_name');
            $table->string('gender')->nullable()->after('dob');
            $table->string('city')->nullable()->after('gender');
            $table->string('blood_group')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
        });

        Schema::table('booking_persons', function (Blueprint $table) {
            $table->dropColumn([
                'surname',
                'given_name',
                'father_name',
                'dob',
                'gender',
                'city',
                'blood_group',
            ]);
        });
    }
};
