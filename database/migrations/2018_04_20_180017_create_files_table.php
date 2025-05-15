<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('files')) {
            Schema::create('files', function (Blueprint $table) {
                $table->increments('id');
                $table->string('relate_type', 10);
                $table->integer('relate_id');
                $table->string('path', 100);
                $table->string('name', 60);
                $table->integer('size');
                $table->integer('user_id');
                $table->integer('created_at');

                $table->index(['relate_type', 'relate_id']);
                $table->index('user_id');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
