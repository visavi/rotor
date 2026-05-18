<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('login');

        DB::table('settings')
            ->where('name', 'loginauthlist')
            ->delete();
    }

    public function down(): void
    {
        Schema::create('login', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('ip', 45)->default('');
            $table->string('brow', 250)->default('');
            $table->string('type', 20)->default('');
            $table->integer('created_at')->default(0);
        });
    }
};
