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
        Schema::table('listmadas', function (Blueprint $table) {
            // On ajoute la colonne row_hash après la colonne time_of_fix (si elle existe)
            $table->string('row_hash')->unique()->nullable()->after('time_of_fix');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('listmadas', function (Blueprint $table) {
            $table->dropColumn('row_hash');
        });
    }
};
