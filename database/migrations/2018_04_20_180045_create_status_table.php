<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('status')) {
            Schema::create('status', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('topoint');
                $table->integer('point');
                $table->string('name', 50);
                $table->string('color', 10)->nullable();

                $table->index('point');
                $table->index('topoint');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('status');
    }
};
