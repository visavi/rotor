<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateVoteanswerTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('voteanswer')) {
            Schema::create('voteanswer', function (Blueprint $table) {
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
        Schema::dropIfExists('voteanswer');
    }
}
