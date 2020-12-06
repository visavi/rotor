<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateLoginTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('login')) {
            $this->schema->create('login', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->string('type', 10);
                $table->integer('created_at');

                $table->index(['user_id', 'created_at']);
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('login');
    }
}
