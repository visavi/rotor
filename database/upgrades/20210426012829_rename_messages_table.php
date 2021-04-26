<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class RenameMessagesTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->schema->rename('messages', 'messages_old');
        $this->schema->rename('messages2', 'messages');
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->rename('messages', 'messages2');
        $this->schema->rename('messages_old', 'messages');
    }
}
