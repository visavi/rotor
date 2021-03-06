<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateChangemailTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('changemail')) {
            Schema::create('changemail', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('mail', 50);
                $table->string('hash', 25);
                $table->integer('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('changemail');
    }
}
