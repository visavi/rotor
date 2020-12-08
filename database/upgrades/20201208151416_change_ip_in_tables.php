<?php

declare(strict_types=1);

use App\Migrations\Migration;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;

final class ChangeIpInTables extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->schema->table('logs', function (Blueprint $table) {
            $table->string('ip', 45)->change();
        });

        $this->schema->table('ban', function (Blueprint $table) {
            $table->string('ip', 45)->change();
        });

        $this->schema->table('chats', function (Blueprint $table) {
            $table->string('ip', 45)->change();
        });

        $this->schema->table('comments', function (Blueprint $table) {
            $table->string('ip', 45)->change();
        });

        $this->schema->table('errors', function (Blueprint $table) {
            $table->string('ip', 45)->change();
        });

        $this->schema->table('guestbook', function (Blueprint $table) {
            $table->string('ip', 45)->change();
        });

        $this->schema->table('login', function (Blueprint $table) {
            $table->string('ip', 45)->change();
        });

        $this->schema->table('online', function (Blueprint $table) {
            $table->string('ip', 45)->change();
        });

        $this->schema->table('posts', function (Blueprint $table) {
            $table->string('ip', 45)->change();
        });

        $this->schema->table('readers', function (Blueprint $table) {
            $table->string('ip', 45)->change();
        });

        $this->schema->table('users', function (Blueprint $table) {
            $table->string('email', 100)->change();
            $table->string('level', 20)->default(USER::PENDED)->change();
        });
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
    }
}
