<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('stickers')) {
            Schema::create('stickers', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('category_id');
                $table->string('name', 100);
                $table->string('code', 20);

                $table->index('code');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stickers');
    }
};
