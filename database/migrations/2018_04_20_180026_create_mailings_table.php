<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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

    public function down(): void
    {
        Schema::dropIfExists('mailings');
    }
};
