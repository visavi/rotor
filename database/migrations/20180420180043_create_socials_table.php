<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateSocialsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('socials')) {
            $this->schema->create('socials', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('network');
                $table->string('uid');
                $table->integer('created_at');

                $table->index('user_id');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('socials');
    }
}
