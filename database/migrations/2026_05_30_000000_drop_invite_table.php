<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Удаление функционала приглашений
     */
    public function up(): void
    {
        Schema::dropIfExists('invite');

        DB::table('settings')->whereIn('name', [
            'invite', 'invite_days', 'invite_rating', 'invite_count', 'listinvite',
        ])->delete();

        DB::table('notices')->where('type', 'invite')->delete();
    }

    /**
     * Восстановление функционала приглашений
     */
    public function down(): void
    {
        if (! Schema::hasTable('invite')) {
            Schema::create('invite', function (Blueprint $table) {
                $table->increments('id');
                $table->string('hash', 16);
                $table->integer('user_id');
                $table->integer('invite_user_id')->nullable();
                $table->boolean('used')->default(false);
                $table->integer('used_at')->nullable();
                $table->integer('created_at');

                $table->index('used');
                $table->index('user_id');
                $table->index('created_at');
                $table->index(['user_id', 'used', 'created_at']);
            });
        }

        DB::table('settings')->insertOrIgnore([
            ['name' => 'invite', 'value' => 0],
            ['name' => 'invite_days', 'value' => 30],
            ['name' => 'invite_rating', 'value' => 10],
            ['name' => 'invite_count', 'value' => 3],
            ['name' => 'listinvite', 'value' => 20],
        ]);
    }
};
