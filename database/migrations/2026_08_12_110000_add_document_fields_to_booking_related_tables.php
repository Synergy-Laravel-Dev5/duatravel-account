<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('cnic_front')->nullable()->after('emergency_phone');
            $table->string('cnic_back')->nullable()->after('cnic_front');
            $table->string('passport_photo')->nullable()->after('cnic_back');
            $table->string('photo')->nullable()->after('passport_photo');
            $table->string('medical_certificate')->nullable()->after('photo');
            $table->string('nominee_name')->nullable()->after('medical_certificate');
            $table->string('nominee_relation')->nullable()->after('nominee_name');
            $table->string('nominee_cnic')->nullable()->after('nominee_relation');
            $table->string('nominee_mobile')->nullable()->after('nominee_cnic');
            $table->string('flight_attachment')->nullable()->after('arrival_pnr');
        });

        Schema::table('booking_persons', function (Blueprint $table) {
            $table->string('cnic_front')->nullable()->after('phone');
            $table->string('cnic_back')->nullable()->after('cnic_front');
            $table->string('passport_photo')->nullable()->after('cnic_back');
            $table->string('photo')->nullable()->after('passport_photo');
            $table->string('medical_certificate')->nullable()->after('photo');
            $table->string('nominee_name')->nullable()->after('medical_certificate');
            $table->string('nominee_relation')->nullable()->after('nominee_name');
            $table->string('nominee_cnic')->nullable()->after('nominee_relation');
            $table->string('nominee_mobile')->nullable()->after('nominee_cnic');
        });

        Schema::table('booking_hotels', function (Blueprint $table) {
            $table->string('hotel_voucher')->nullable()->after('no_of_rooms');
        });

        Schema::table('booking_transports', function (Blueprint $table) {
            $table->string('transport_ticket')->nullable()->after('notes');
        });

        Schema::table('booking_visas', function (Blueprint $table) {
            $table->string('visa_attachment')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'cnic_front', 'cnic_back', 'passport_photo', 'photo', 'medical_certificate',
                'nominee_name', 'nominee_relation', 'nominee_cnic', 'nominee_mobile', 'flight_attachment'
            ]);
        });

        Schema::table('booking_persons', function (Blueprint $table) {
            $table->dropColumn([
                'cnic_front', 'cnic_back', 'passport_photo', 'photo', 'medical_certificate',
                'nominee_name', 'nominee_relation', 'nominee_cnic', 'nominee_mobile'
            ]);
        });

        Schema::table('booking_hotels', function (Blueprint $table) {
            $table->dropColumn('hotel_voucher');
        });

        Schema::table('booking_transports', function (Blueprint $table) {
            $table->dropColumn('transport_ticket');
        });

        Schema::table('booking_visas', function (Blueprint $table) {
            $table->dropColumn('visa_attachment');
        });
    }
};
