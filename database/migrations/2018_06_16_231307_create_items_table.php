<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('board_id');
                $table->string('title');
                $table->text('text');
                $table->integer('user_id');
                $table->integer('price')->default(0);
                $table->string('phone', 15)->nullable();
                $table->boolean('active')->default(true);
                $table->integer('created_at');
                $table->integer('updated_at');
                $table->integer('expires_at');

                $table->index('board_id');
                $table->index('expires_at');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
