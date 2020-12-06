<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateMailingsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('mailings')) {
            $this->schema->create('mailings', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('type', 30);
                $table->string('subject', 100);
                $table->text('text');
                $table->boolean('sent')->default(false);
                $table->integer('sent_at')->nullable();
                $table->integer('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('mailings');
    }
}
