<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('modules')) {
            Schema::create('modules', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->string('version', 10);
                $table->boolean('active')->default(true);
                $table->json('settings')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
