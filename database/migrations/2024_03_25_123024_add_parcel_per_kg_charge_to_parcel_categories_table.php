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
        Schema::table('parcel_categories', function (Blueprint $table) {
            $table->double('parcel_per_kg_charge',23,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parcel_categories', function (Blueprint $table) {
            $table->dropColumn('parcel_per_kg_charge');
        });
    }
};
