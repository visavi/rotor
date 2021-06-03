<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateContactsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('contact_id');
                $table->text('text')->nullable();
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
        Schema::dropIfExists('contacts');
    }
}
