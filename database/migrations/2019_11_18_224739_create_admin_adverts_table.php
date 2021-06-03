<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateAdminAdvertsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('admin_adverts')) {
            Schema::create('admin_adverts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('site', 100);
                $table->string('name', 50);
                $table->string('color', 10)->nullable();
                $table->boolean('bold')->default(false);
                $table->integer('user_id');
                $table->integer('created_at');
                $table->integer('deleted_at')->nullable();
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_adverts');
    }
}
