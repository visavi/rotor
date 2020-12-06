<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateStickersTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('stickers')) {
            $this->schema->create('stickers', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('category_id');
                $table->string('name', 100);
                $table->string('code', 20);

                $table->index('code');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('stickers');
    }
}
