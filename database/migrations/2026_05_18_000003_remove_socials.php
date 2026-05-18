<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('socials');
    }

    public function down(): void
    {
        Schema::create('socials', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('network', 50)->default('');
            $table->string('network_id', 100)->default('');
            $table->timestamps();
        });
    }
};
