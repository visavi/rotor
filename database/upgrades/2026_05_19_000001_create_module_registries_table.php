<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('module_registries')) {
            return;
        }

        Schema::create('module_registries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
            $table->string('name')->default('');
            $table->boolean('active')->default(true);
            $table->longText('cached_data')->nullable();
            $table->dateTime('cached_at')->nullable();
            $table->datetimes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_registries');
    }
};
