<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateGuestbookTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('guestbook')) {
            $this->schema->create('guestbook', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->nullable();
                $table->text('text');
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->text('reply')->nullable();
                $table->string('guest_name', 20)->nullable();
                $table->integer('edit_user_id')->nullable();
                $table->integer('updated_at')->nullable();
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
        $this->schema->dropIfExists('guestbook');
    }
}
