<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateStickersCategoriesTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('stickers_categories')) {
            $this->schema->create('stickers_categories', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->integer('updated_at')->nullable();
                $table->integer('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('stickers_categories');
    }
}
