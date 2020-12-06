<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateChangemailTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('changemail')) {
            $this->schema->create('changemail', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('mail', 50);
                $table->string('hash', 25);
                $table->integer('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('changemail');
    }
}
