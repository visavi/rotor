<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('chats')) {
            Schema::create('chats', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->text('text');
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->integer('edit_user_id')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('created_at');

                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
