<?php

use Phinx\Migration\AbstractMigration;

class CreateOffersTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('offers')) {
            $table = $this->table('offers', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('type', 'string', ['limit' => 20])
                ->addColumn('title', 'string', ['limit' => 50])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('user_id', 'integer')
                ->addColumn('rating', 'integer', ['default' => 0])
                ->addColumn('created_at', 'integer')
                ->addColumn('status', 'string', ['limit' => 20])
                ->addColumn('count_comments', 'integer', ['default' => 0])
                ->addColumn('closed', 'boolean', ['default' => 0])
                ->addColumn('reply', 'text', ['null' => true])
                ->addColumn('reply_user_id', 'integer', ['null' => true])
                ->addColumn('updated_at', 'integer', ['null' => true])
                ->addIndex('created_at')
                ->addIndex('rating')
                ->create();
        }
    }
}
