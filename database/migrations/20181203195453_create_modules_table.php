<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateModulesTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('modules')) {
            $this->schema->create('modules', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->string('version', 10);
                $table->boolean('disabled')->default(false);
                $table->integer('updated_at')->nullable();
                $table->integer('created_at');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('modules');
    }
}
