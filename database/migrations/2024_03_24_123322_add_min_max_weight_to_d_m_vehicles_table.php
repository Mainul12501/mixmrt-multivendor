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
        Schema::table('d_m_vehicles', function (Blueprint $table) {
            $table->double('minimum_weight',16,3)->default(0);
            $table->double('maximum_weight',16,3)->default(0);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('d_m_vehicles', function (Blueprint $table) {
            $table->dropColumn('minimum_weight')->default(0);
            $table->dropColumn('maximum_weight')->default(0);

            
        });
    }
};
