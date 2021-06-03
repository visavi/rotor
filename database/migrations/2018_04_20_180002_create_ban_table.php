<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateBanTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('ban')) {
            Schema::create('ban', function (Blueprint $table) {
                $table->increments('id');
                $table->ipAddress('ip');
                $table->integer('user_id')->nullable();
                $table->integer('created_at');

                $table->unique('ip');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('ban');
    }
}
