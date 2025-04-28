<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('votes')) {
            Schema::create('votes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 100);
                $table->text('description')->nullable();
                $table->integer('count')->default(0);
                $table->boolean('closed')->default(false);
                $table->integer('created_at');
                $table->integer('topic_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
