<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreatePostsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('posts')) {
            $this->schema->create('posts', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('topic_id');
                $table->integer('user_id');
                $table->text('text');
                $table->integer('rating')->default(0);
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->integer('edit_user_id')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('created_at');

                $table->index(['topic_id', 'created_at']);
                $table->index(['user_id', 'created_at']);
                $table->index(['rating', 'created_at']);
                $table->index('created_at');
            });

            if (config('database.default') === 'mysql') {
                $this->db->getConnection()->statement('CREATE FULLTEXT INDEX text ON posts(text);');
            }
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('posts');
    }
}
