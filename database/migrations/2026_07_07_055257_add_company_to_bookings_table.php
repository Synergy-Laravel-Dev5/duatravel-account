<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
            $table->string('booking_for')->default('client')->after('company_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn('booking_for');
            $table->foreignId('client_id')->nullable(false)->change();
        });
    }
};
