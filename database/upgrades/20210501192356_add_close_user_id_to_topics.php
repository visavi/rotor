<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class AddCloseUserIdToTopics extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->schema->table('topics', function (Blueprint $table) {
            $table->integer('close_user_id')->nullable()->after('last_post_id');
        });
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->table('topics', function (Blueprint $table) {
            $table->dropColumn('close_user_id');
        });
    }
}
