<?php

use Phinx\Migration\AbstractMigration;

class RemoveInboxTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('inbox');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if (! $this->hasTable('inbox')) {
            $table = $this->table('inbox', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('author_id', 'integer', ['null' => true])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id')
                ->addIndex('created_at')
                ->create();
        }
    }
}
