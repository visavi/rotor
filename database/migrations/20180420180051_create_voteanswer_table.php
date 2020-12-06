<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateVoteanswerTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('voteanswer')) {
            $this->schema->create('voteanswer', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('vote_id');
                $table->string('answer', 50);
                $table->integer('result')->default(0);
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('voteanswer');
    }
}
