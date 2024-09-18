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
        Schema::table('disbursement_withdrawal_methods', function (Blueprint $table) {
            Schema::table('disbursement_withdrawal_methods', function (Blueprint $table) {
                $table->text('store_name')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       //
    }
};
