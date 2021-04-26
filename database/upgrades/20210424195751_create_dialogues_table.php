<?php

declare(strict_types=1);

use App\Migrations\Migration;
use App\Models\Message;
use Illuminate\Database\Schema\Blueprint;

final class CreateDialoguesTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('dialogues')) {
            $this->schema->create('dialogues', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('message_id');
                $table->integer('user_id');
                $table->integer('author_id');
                $table->enum('type', [Message::IN, Message::OUT]);
                $table->boolean('reading')->default(false);
                $table->integer('created_at');
                $table->string('hash', 30);

                $table->index('hash');
                $table->index(['user_id', 'author_id']);
                $table->index(['message_id', 'created_at']);
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('dialogues');
    }
}
