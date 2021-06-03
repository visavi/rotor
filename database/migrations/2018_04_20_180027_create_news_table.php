<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateNewsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('news')) {
            Schema::create('news', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 100);
                $table->text('text');
                $table->integer('user_id');
                $table->string('image', 100)->nullable();
                $table->integer('count_comments')->default(0);
                $table->boolean('closed')->default(false);
                $table->boolean('top')->default(false);
                $table->integer('rating')->default(0);
                $table->integer('created_at');

                $table->index('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
}
