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
        Schema::table('offline_payments', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->change();
            $table->foreignId('store_id')->nullable();
            $table->foreignId('delivery_man_id')->nullable();
            $table->decimal('amount',23,2)->default(0);
            $table->string('type')->default('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offline_payments', function (Blueprint $table) {
            $table->dropColumn('order_id');
            $table->dropColumn('store_id');
            $table->dropColumn('delivery_man_id');
            $table->dropColumn('amount');
            $table->dropColumn('type');
        });
    }
};
