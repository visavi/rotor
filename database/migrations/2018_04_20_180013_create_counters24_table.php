<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('counters24')) {
            Schema::create('counters24', function (Blueprint $table) {
                $table->increments('id');
                $table->dateTime('period');
                $table->integer('hosts');
                $table->integer('hits');

                $table->unique('period');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('counters24');
    }
};
