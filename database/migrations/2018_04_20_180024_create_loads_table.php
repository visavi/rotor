<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('loads')) {
            Schema::create('loads', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('sort')->default(0);
                $table->integer('parent_id')->default(0);
                $table->string('name', 100);
                $table->integer('count_downs')->default(0);
                $table->boolean('closed')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loads');
    }
};
