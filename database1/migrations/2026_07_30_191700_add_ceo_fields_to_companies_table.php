<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('ceo_name')->nullable();
            $table->string('ceo_cnic_front')->nullable();
            $table->string('ceo_cnic_back')->nullable();
            $table->string('ceo_shares_percent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['ceo_name', 'ceo_cnic_front', 'ceo_cnic_back', 'ceo_shares_percent']);
        });
    }
};
