<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('rating')) {
            Schema::create('rating', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('recipient_id');
                $table->text('text');
                $table->string('vote', 1);
                $table->integer('created_at');

                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rating');
    }
};
