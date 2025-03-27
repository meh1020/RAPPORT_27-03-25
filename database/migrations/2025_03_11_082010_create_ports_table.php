<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom du port
            $table->timestamps();
        });

        Schema::create('destination_port', function (Blueprint $table) {
            $table->id();
            $table->foreignId('port_id')->constrained()->onDelete('cascade');
            $table->foreignId('destination_id')->constrained()->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('destination_port');
        Schema::dropIfExists('ports');
    }
};
