<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class AddDescriptionToVotes extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->schema->table('votes', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
        });
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->table('votes', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
