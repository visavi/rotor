<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class ChangeTextInContacts extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->text('text')->nullable()->change();
        });
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->text('text')->change();
        });
    }
}
