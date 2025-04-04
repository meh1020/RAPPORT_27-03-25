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
        Schema::table('zone_2s', function (Blueprint $table) {
            // Ajout de la colonne row_hash unique et nullable pour détecter les doublons
            $table->string('row_hash')->unique()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zone_2s', function (Blueprint $table) {
            $table->dropColumn('row_hash');
        });
    }
};
