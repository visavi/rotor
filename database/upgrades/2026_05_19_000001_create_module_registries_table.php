<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('module_registries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
            $table->string('name')->default('');
            $table->boolean('active')->default(true);
            $table->text('cached_data')->nullable();
            $table->timestamp('cached_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_registries');
    }
};
