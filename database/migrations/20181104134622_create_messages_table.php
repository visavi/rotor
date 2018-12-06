<?php

use Phinx\Migration\AbstractMigration;

class CreateMessagesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('messages', ['collation' => env('DB_COLLATION')]);
        $table
            ->addColumn('user_id', 'integer')
            ->addColumn('author_id', 'integer')
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('type', 'enum', ['values' => ['in', 'out']])
            ->addColumn('reading', 'boolean', ['default' => 0])
            ->addColumn('created_at', 'integer')
            ->addIndex(['user_id', 'author_id'], ['name' => 'user_id'])
            ->addIndex('created_at')
            ->create();
    }
}
