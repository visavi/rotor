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
                $table->string('version', 15);
                $table->boolean('active')->default(true);
                $table->datetimes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
