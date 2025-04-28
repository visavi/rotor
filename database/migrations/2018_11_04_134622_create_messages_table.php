<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('author_id');
                $table->text('text');
                $table->integer('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
