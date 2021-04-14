<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class ChangeVoteInPollings extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->schema->table('pollings', function (Blueprint $table) {
            $table->string('vote')->change();
        });
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->table('pollings', function (Blueprint $table) {
            $table->string('vote', 1)->change();
        });
    }
}
