<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateAdvertsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('adverts')) {
            $this->schema->create('adverts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('site', 100);
                $table->string('name', 50);
                $table->string('color', 10)->nullable();
                $table->boolean('bold')->default(false);
                $table->integer('user_id');
                $table->integer('created_at');
                $table->integer('deleted_at')->nullable();
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('adverts');
    }
}
