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
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('ignores');

        DB::table('settings')
            ->whereIn('name', ['contactlist', 'ignorlist', 'limitcontact', 'limitignore'])
            ->delete();

        DB::table('notices')
            ->whereIn('type', ['contact', 'ignore'])
            ->delete();
    }

    public function down(): void
    {
        Schema::create('contacts', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('to_user_id');
            $table->timestamps();
        });

        Schema::create('ignores', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('to_user_id');
            $table->timestamps();
        });
    }
};
