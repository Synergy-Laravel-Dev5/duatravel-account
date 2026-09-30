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
        Schema::table('quotation_accommodations', function (Blueprint $table) {
            $table->decimal('additional_amount', 15, 2)->default(0)->after('selling_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_accommodations', function (Blueprint $table) {
            $table->dropColumn('additional_amount');
        });
    }
};
