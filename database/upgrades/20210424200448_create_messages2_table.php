<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateMessages2Table extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('messages2')) {
            $this->schema->create('messages2', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('author_id');
                $table->text('text');
                $table->integer('created_at');
                $table->string('hash', 30);

                $table->index('hash');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('messages2');
    }
}
