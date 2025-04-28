<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('online')) {
            Schema::create('online', function (Blueprint $table) {
                $table->string('uid', 32)->primary();
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->integer('user_id')->nullable();
                $table->integer('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('online');
    }
};
