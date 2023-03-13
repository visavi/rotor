<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateInviteTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
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
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('invite');
    }
}
