<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('photos')) {
            Schema::create('photos', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('title', 50);
                $table->text('text');
                $table->integer('rating')->default(0);
                $table->boolean('closed')->default(false);
                $table->integer('count_comments')->default(0);
                $table->integer('created_at');

                $table->index('user_id');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
