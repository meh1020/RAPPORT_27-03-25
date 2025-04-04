<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zone_9s', function (Blueprint $table) {
            $table->string('row_hash')->unique()->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('zone_9s', function (Blueprint $table) {
            $table->dropColumn('row_hash');
        });
    }
};
