<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateBlacklistTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('blacklist')) {
            Schema::create('blacklist', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type', 30);
                $table->string('value', 100);
                $table->integer('user_id');
                $table->integer('created_at');

                $table->index('type');
                $table->index('value');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklist');
    }
}
