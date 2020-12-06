<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateStatusTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('status')) {
            $this->schema->create('status', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('topoint');
                $table->integer('point');
                $table->string('name', 50);
                $table->string('color', 10)->nullable();

                $table->index('point');
                $table->index('topoint');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('status');
    }
}
