<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('search', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->string('relate_type', 10);
            $table->integer('relate_id');
            $table->integer('created_at');

            $table->fullText(['text']);
            $table->unique(['relate_type', 'relate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search');
    }
};
