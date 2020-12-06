<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateCountersTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('counters')) {
            $this->schema->create('counters', function (Blueprint $table) {
                $table->increments('id');
                $table->dateTime('period');
                $table->integer('allhosts');
                $table->integer('allhits');
                $table->integer('dayhosts');
                $table->integer('dayhits');
                $table->integer('hosts24');
                $table->integer('hits24');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('counters');
    }
}
