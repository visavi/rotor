<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('voteanswer')) {
            Schema::create('voteanswer', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('vote_id');
                $table->string('answer', 50);
                $table->integer('result')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('voteanswer');
    }
};
