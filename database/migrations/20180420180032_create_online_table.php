<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateOnlineTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('online')) {
            $this->schema->create('online', function (Blueprint $table) {
                $table->string('uid', 32)->primary();
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->integer('user_id')->nullable();
                $table->integer('updated_at')->nullable();
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('online');
    }
}
