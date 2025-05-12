<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('downs')) {
            Schema::create('downs', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('category_id');
                $table->string('title', 100);
                $table->text('text')->nullable();
                $table->integer('user_id');
                $table->integer('count_comments')->default(0);
                $table->integer('rating')->default(0);
                $table->integer('loads')->default(0);
                $table->boolean('active')->default(false);
                $table->json('links')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('created_at');

                $table->index('category_id');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('downs');
    }
};
