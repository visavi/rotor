<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateBlogsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('blogs')) {
            Schema::create('blogs', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('sort')->default(0);
                $table->integer('parent_id')->default(0);
                $table->string('name', 100);
                $table->integer('count_articles')->default(0);
                $table->boolean('closed')->default(false);
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
}
