<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateContactsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('contacts')) {
            $this->schema->create('contacts', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('contact_id');
                $table->text('text');
                $table->integer('created_at');

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
        $this->schema->dropIfExists('contacts');
    }
}
