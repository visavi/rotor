<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('forums')) {
            Schema::create('forums', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('sort')->default(0);
                $table->integer('parent_id')->default(0);
                $table->string('title');
                $table->string('description')->nullable();
                $table->integer('last_topic_id')->default(0);
                $table->boolean('closed')->default(false);
                $table->integer('count_topics')->default(0);
                $table->integer('count_posts')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('forums');
    }
};
