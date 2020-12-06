<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateVotesTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('votes')) {
            $this->schema->create('votes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 100);
                $table->integer('count')->default(0);
                $table->boolean('closed')->default(false);
                $table->integer('created_at');
                $table->integer('topic_id')->nullable();
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('votes');
    }
}
