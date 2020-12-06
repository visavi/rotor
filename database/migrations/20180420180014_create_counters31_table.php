<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateCounters31Table extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('counters31')) {
            $this->schema->create('counters31', function (Blueprint $table) {
                $table->increments('id');
                $table->dateTime('period');
                $table->integer('hosts');
                $table->integer('hits');

                $table->unique('period');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('counters31');
    }
}
