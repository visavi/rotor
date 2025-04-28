<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('user_data')) {
            Schema::create('user_data', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('field_id');
                $table->text('value')->nullable();

                $table->unique(['user_id', 'field_id']);
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_data');
    }
};
