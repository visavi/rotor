<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateMailingsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('mailings')) {
            Schema::create('mailings', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('type', 30);
                $table->string('subject', 100);
                $table->text('text');
                $table->boolean('sent')->default(false);
                $table->integer('sent_at')->nullable();
                $table->integer('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailings');
    }
}
