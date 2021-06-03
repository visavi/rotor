<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateLogsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('logs')) {
            Schema::create('logs', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('request')->nullable();
                $table->string('referer')->nullable();
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->integer('created_at');

                $table->index('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
}
