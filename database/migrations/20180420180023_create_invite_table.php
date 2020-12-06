<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateInviteTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('invite')) {
            $this->schema->create('invite', function (Blueprint $table) {
                $table->increments('id');
                $table->string('hash', 16);
                $table->integer('user_id');
                $table->integer('invite_user_id')->nullable();
                $table->boolean('used')->default(false);
                $table->integer('created_at');

                $table->index('used');
                $table->index('user_id');
                $table->index('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('invite');
    }
}
