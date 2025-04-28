<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('paid_adverts')) {
            Schema::create('paid_adverts', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('place', 20);
                $table->string('site', 100);
                $table->json('names');
                $table->string('color', 10)->nullable();
                $table->boolean('bold')->default(false);
                $table->string('comment')->nullable();
                $table->integer('created_at');
                $table->integer('deleted_at')->nullable();
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('paid_adverts');
    }
};
