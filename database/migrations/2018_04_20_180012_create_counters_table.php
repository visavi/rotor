<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateCountersTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('counters')) {
            Schema::create('counters', function (Blueprint $table) {
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
        Schema::dropIfExists('counters');
    }
}
