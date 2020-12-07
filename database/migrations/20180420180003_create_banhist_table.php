<?php

declare(strict_types=1);

use App\Migrations\Migration;
use App\Models\Banhist;
use Illuminate\Database\Schema\Blueprint;

final class CreateBanhistTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('banhist')) {
            $this->schema->create('banhist', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('send_user_id');
                $table->enum('type', [Banhist::BAN, Banhist::UNBAN, Banhist::CHANGE]);
                $table->text('reason');
                $table->integer('term')->default(0);
                $table->integer('created_at');
                $table->boolean('explain')->default(false);

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
        $this->schema->dropIfExists('banhist');
    }
}
