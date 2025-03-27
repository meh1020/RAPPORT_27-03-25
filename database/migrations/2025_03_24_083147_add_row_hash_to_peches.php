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
        Schema::table('peches', function (Blueprint $table) {
            // On ajoute la colonne row_hash qui sera utilisée pour détecter les doublons
            $table->string('row_hash')->unique()->nullable();
        });
    }

    public function down()
    {
        Schema::table('peches', function (Blueprint $table) {
            $table->dropColumn('row_hash');
        });
    }
};
