<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateSpamTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('spam')) {
            $this->schema->create('spam', function (Blueprint $table) {
                $table->increments('id');
                $table->string('relate_type', 10);
                $table->integer('relate_id');
                $table->integer('user_id');
                $table->string('path', 100)->nullable();
                $table->integer('created_at');

                $table->index('created_at');
                $table->index(['relate_type', 'relate_id']);
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('spam');
    }
}
