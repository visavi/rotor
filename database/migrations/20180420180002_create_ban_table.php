<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateBanTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('ban')) {
            $this->schema->create('ban', function (Blueprint $table) {
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
        $this->schema->dropIfExists('ban');
    }
}
