<?php

declare(strict_types=1);

use App\Migrations\Migration;
use App\Models\Message;
use Illuminate\Database\Schema\Blueprint;

final class CreateMessagesTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('messages')) {
            $this->schema->create('messages', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('author_id');
                $table->text('text');
                $table->enum('type', [Message::IN, Message::OUT]);
                $table->boolean('reading')->default(false);
                $table->integer('created_at');

                $table->index(['user_id', 'author_id']);
                $table->index('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('messages');
    }
}
