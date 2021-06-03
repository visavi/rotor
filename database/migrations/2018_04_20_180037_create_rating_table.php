<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateRatingTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('rating')) {
            Schema::create('rating', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('recipient_id');
                $table->text('text');
                $table->string('vote', 1);
                $table->integer('created_at');

                $table->index('user_id');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating');
    }
}
