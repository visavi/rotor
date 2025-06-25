<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('changemail');
    }

    public function down(): void
    {
        Schema::create('changemail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('mail', 50);
            $table->string('hash', 25);
            $table->integer('created_at');
        });
    }
};
