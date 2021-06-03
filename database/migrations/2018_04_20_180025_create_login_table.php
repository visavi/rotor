<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateLoginTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('login')) {
            Schema::create('login', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->string('type', 10);
                $table->integer('created_at');

                $table->index(['user_id', 'created_at']);
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('login');
    }
}
