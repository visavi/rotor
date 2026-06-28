<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('stickers_categories')) {
            Schema::create('stickers_categories', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stickers_categories');
    }
};
