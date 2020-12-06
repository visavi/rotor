<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateIgnoringTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('ignoring')) {
            $this->schema->create('ignoring', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('ignore_id');
                $table->text('text')->nullable();
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
        $this->schema->dropIfExists('ignoring');
    }
}
