<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateReadersTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('readers')) {
            Schema::create('readers', function (Blueprint $table) {
                $table->increments('id');
                $table->string('relate_type', 10);
                $table->integer('relate_id');
                $table->ipAddress('ip');
                $table->integer('created_at');

                $table->index(['relate_type', 'relate_id', 'ip']);
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('readers');
    }
}
