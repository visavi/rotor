<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateOffersTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('offers')) {
            $this->schema->create('offers', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type', 20);
                $table->string('title', 50);
                $table->text('text');
                $table->integer('user_id');
                $table->integer('rating')->default(0);
                $table->string('status', 20);
                $table->integer('count_comments')->default(0);
                $table->boolean('closed')->default(false);
                $table->text('reply')->nullable();
                $table->integer('reply_user_id')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('created_at');

                $table->index('rating');
                $table->index('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('offers');
    }
}
