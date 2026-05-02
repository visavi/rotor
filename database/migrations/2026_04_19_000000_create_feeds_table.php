<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->id();
            $table->string('relate_type', 20);
            $table->unsignedInteger('relate_id');
            $table->unsignedInteger('created_at');

            $table->unique(['relate_type', 'relate_id']);
            $table->index('created_at');
            $table->index(['relate_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
};
