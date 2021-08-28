<?php

declare(strict_types=1);

use App\Models\UserField;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateUserFieldsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('user_fields')) {
            Schema::create('user_fields', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('sort');
                $table->enum('type', [UserField::INPUT, UserField::TEXTAREA]);
                $table->string('name', 50);
                $table->integer('min');
                $table->integer('max');
                $table->boolean('required')->default(false);
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_fields');
    }
}
