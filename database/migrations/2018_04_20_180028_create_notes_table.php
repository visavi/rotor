<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('notes')) {
            Schema::create('notes', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->text('text')->nullable();
                $table->integer('edit_user_id');
                $table->integer('updated_at');

                $table->unique('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
