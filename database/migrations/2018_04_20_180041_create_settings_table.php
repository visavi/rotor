<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->string('name', 25)->primary();
                $table->string('value');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
