<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('counters')) {
            Schema::create('counters', function (Blueprint $table) {
                $table->increments('id');
                $table->dateTime('period');
                $table->integer('allhosts');
                $table->integer('allhits');
                $table->integer('dayhosts');
                $table->integer('dayhits');
                $table->integer('hosts24');
                $table->integer('hits24');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('counters');
    }
};
