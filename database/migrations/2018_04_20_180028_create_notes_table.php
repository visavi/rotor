<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateNotesTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('notes')) {
            Schema::create('notes', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->text('text');
                $table->integer('edit_user_id');
                $table->integer('updated_at');

                $table->unique('user_id');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
}
