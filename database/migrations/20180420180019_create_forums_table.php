<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateForumsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('forums')) {
            $this->schema->create('forums', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('sort')->default(0);
                $table->integer('parent_id')->default(0);
                $table->string('title', 50);
                $table->string('description', 100)->nullable();
                $table->integer('last_topic_id')->default(0);
                $table->boolean('closed')->default(false);
                $table->integer('count_topics')->default(0);
                $table->integer('count_posts')->default(0);
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('forums');
    }
}
