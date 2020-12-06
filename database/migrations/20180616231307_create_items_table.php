<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateItemsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('items')) {
            $this->schema->create('items', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('board_id');
                $table->string('title', 100);
                $table->text('text');
                $table->integer('user_id');
                $table->integer('price')->default(0);
                $table->string('phone', 15)->nullable();
                $table->integer('created_at');
                $table->integer('updated_at');
                $table->integer('expires_at');

                $table->index('board_id');
                $table->index('expires_at');
                $table->index('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('items');
    }
}
