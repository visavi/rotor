<?php

declare(strict_types=1);

use App\Models\Message;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateDialoguesTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('dialogues')) {
            Schema::create('dialogues', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('message_id');
                $table->integer('user_id');
                $table->integer('author_id');
                $table->enum('type', [Message::IN, Message::OUT]);
                $table->boolean('reading')->default(false);
                $table->integer('created_at');

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
        Schema::dropIfExists('dialogues');
    }
}
