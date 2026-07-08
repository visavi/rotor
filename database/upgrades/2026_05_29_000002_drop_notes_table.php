<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('notes');
    }

    public function down(): void
    {
        if (! Schema::hasTable('notes')) {
            Schema::create('notes', function ($table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->text('text')->nullable();
                $table->integer('edit_user_id');
                $table->integer('updated_at');
                $table->unique('user_id');
            });
        }
    }
};
