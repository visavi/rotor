<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('adverts')) {
            Schema::create('adverts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('site', 100);
                $table->string('name', 50);
                $table->string('color', 10)->nullable();
                $table->boolean('bold')->default(false);
                $table->integer('user_id');
                $table->integer('created_at');
                $table->integer('deleted_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('adverts');
    }
};
