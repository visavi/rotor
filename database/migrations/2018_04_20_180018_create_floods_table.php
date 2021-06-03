<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateFloodsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('floods')) {
            Schema::create('floods', function (Blueprint $table) {
                $table->increments('id');
                $table->string('uid', 32);
                $table->string('page', 30);
                $table->integer('attempts')->default(0);
                $table->integer('created_at');

                $table->index('uid');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('floods');
    }
}
