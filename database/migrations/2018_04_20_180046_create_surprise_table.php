<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateSurpriseTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('surprise')) {
            Schema::create('surprise', function (Blueprint $table) {
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
        Schema::dropIfExists('surprise');
    }
}
