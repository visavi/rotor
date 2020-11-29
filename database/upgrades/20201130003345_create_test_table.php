<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTestTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->schema->create('test', function (Blueprint $table) {
            $table->increments('id');
            $table->string('dialog_uuid', 32);
            $table->string('user_uuid', 32)->nullable();
            $table->boolean('finished')->default(false);
            $table->boolean('sent')->default(false);
            $table->ipAddress('ip');
            $table->enum('xxx', ['a', 'b', 'c']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('test');
    }
}
