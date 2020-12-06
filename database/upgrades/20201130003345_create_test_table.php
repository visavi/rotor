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
        if (! $this->schema->hasTable('test')) {
            $this->schema->create('test', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('category_id');
                $table->string('title', 100);
                $table->text('text');
                $table->integer('user_id');
                $table->integer('count_comments')->default(0);
                $table->integer('rating')->default(0);
                $table->integer('rated')->default(0);
                $table->integer('loads')->default(0);
                $table->boolean('active')->default(false);
                $table->integer('updated_at')->nullable();
                $table->integer('created_at');

                $table->index('category_id');
                $table->index('created_at');
            });

            if (config('DB_DRIVER') === 'mysql') {
                $this->db->getConnection()->statement('CREATE FULLTEXT INDEX title ON test(title);');
                $this->db->getConnection()->statement('CREATE FULLTEXT INDEX text ON test(text);');
            }
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('test');
    }

    /**
     * Migrate Up.
     */
/*    public function up(): void
    {
        if (! $this->schema->hasTable('test')) {
            $this->schema->create('test', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('request')->nullable();
                $table->string('referer')->nullable();
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->integer('created_at');
                $table->string('relate_type', 10);
                $table->integer('relate_id');
                $table->boolean('explain')->default(false);
                $table->text('text');

                $table->index('created_at');
                $table->index(['relate_type', 'relate_id']);
            });
        }
    }*/

    /**
     * Migrate Down.
     */
/*    public function down(): void
    {
        $this->schema->dropIfExists('test');
    }*/
}
