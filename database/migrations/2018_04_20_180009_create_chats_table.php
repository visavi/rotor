<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateChatsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('chats')) {
            Schema::create('chats', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->text('text');
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->integer('edit_user_id')->nullable();
                $table->integer('updated_at')->nullable();
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
        Schema::dropIfExists('chats');
    }
}
