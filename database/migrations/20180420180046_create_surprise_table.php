<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateSurpriseTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('surprise')) {
            $this->schema->create('surprise', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->year('year');
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
        $this->schema->dropIfExists('surprise');
    }
}
