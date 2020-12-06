<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateTransfersTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('transfers')) {
            $this->schema->create('transfers', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('recipient_id');
                $table->text('text');
                $table->integer('total')->default(0);
                $table->integer('created_at');

                $table->index('user_id');
                $table->index('recipient_id');
                $table->index('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('transfers');
    }
}
