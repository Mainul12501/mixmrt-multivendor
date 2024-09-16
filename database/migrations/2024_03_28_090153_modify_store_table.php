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
        Schema::table('stores', function (Blueprint $table) {
            $table->renameColumn('license','tax_document');
            $table->string('registration_document',255);
            $table->string('agreement_document',255);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->renameColumn('tax_document', 'license');
            $table->dropColumn('registration_document');
            $table->dropColumn('agreement_document');
            $table->dropColumn('license');
        });
    }
};
