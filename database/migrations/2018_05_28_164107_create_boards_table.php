<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('boards')) {
            Schema::create('boards', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('sort')->default(0);
                $table->integer('parent_id')->default(0);
                $table->string('name', 100);
                $table->integer('count_items')->default(0);
                $table->boolean('closed')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('boards');
    }
};
