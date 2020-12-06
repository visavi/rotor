<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateErrorsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('errors')) {
            $this->schema->create('errors', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('code');
                $table->string('request')->nullable();
                $table->string('referer')->nullable();
                $table->integer('user_id')->nullable();
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->integer('created_at');

                $table->index(['code', 'created_at']);
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('errors');
    }
}
