<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('notices')) {
            Schema::create('notices', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type', 20);
                $table->string('name', 100);
                $table->text('text');
                $table->integer('user_id');
                $table->boolean('protect')->default(false);
                $table->integer('updated_at')->nullable();
                $table->integer('created_at');

                $table->unique('type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
