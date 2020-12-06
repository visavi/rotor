<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateLogsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('logs')) {
            $this->schema->create('logs', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('request')->nullable();
                $table->string('referer')->nullable();
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->integer('created_at');

                $table->index('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('logs');
    }
}
