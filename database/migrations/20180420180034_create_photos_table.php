<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreatePhotosTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('photos')) {
            $this->schema->create('photos', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('title', 50);
                $table->text('text');
                $table->integer('rating')->default(0);
                $table->boolean('closed')->default(false);
                $table->integer('count_comments')->default(0);
                $table->integer('created_at');

                $table->index('user_id');
                $table->index('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('photos');
    }
}
