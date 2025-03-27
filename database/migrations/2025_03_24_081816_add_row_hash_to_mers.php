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
        Schema::table('mers', function (Blueprint $table) {
            // Vous pouvez ajouter unique() si le hash doit être unique
            $table->string('row_hash')->nullable()->unique();
        });
    }

    public function down()
    {
        Schema::table('mers', function (Blueprint $table) {
            $table->dropColumn('row_hash');
        });
    }

};
