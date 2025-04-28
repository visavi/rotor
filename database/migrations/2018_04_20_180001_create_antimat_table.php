<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('antimat')) {
            Schema::create('antimat', function (Blueprint $table) {
                $table->increments('id');
                $table->string('string', 100);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('antimat');
    }
};
