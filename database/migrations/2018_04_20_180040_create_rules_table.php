<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('rules')) {
            Schema::create('rules', function (Blueprint $table) {
                $table->increments('id');
                $table->text('text');
                $table->integer('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
