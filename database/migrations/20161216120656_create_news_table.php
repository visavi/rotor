<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateNewsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('news')) {
            $table = $this->table('news', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('title', 'string', ['limit' => 100])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('user_id', 'integer')
                ->addColumn('image', 'string', ['limit' => 30, 'null' => true])
                ->addColumn('created_at', 'integer')
                ->addColumn('count_comments', 'integer', ['default' => 0])
                ->addColumn('closed', 'boolean', ['default' => 0])
                ->addColumn('top', 'boolean', ['default' => 0])
                ->addIndex('created_at')
                ->create();
        }
    }
}
