<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class RemoveHashFromDialogues extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->schema->table('dialogues', function (Blueprint $table) {
            $table->dropColumn('hash');
        });

        $this->schema->table('messages2', function (Blueprint $table) {
            $table->dropColumn('hash');
        });
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
    }
}
