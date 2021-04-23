<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class AddColorToUsers extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->string('color', 10)->nullable()->after('status');
        });
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
}
