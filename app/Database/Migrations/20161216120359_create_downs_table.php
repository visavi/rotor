<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateDownsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('downs')) {
            $table = $this->table('downs', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('category_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
                ->addColumn('title', 'string', ['limit' => 100])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('link', 'string', ['limit' => 50])
                ->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('author', 'string', ['limit' => 50])
                ->addColumn('site', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('screen', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('time', 'integer')
                ->addColumn('comments', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
                ->addColumn('rating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
                ->addColumn('rated', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
                ->addColumn('loads', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
                ->addColumn('last_load', 'integer', ['default' => 0])
                ->addColumn('app', 'boolean', ['default' => false])
                ->addColumn('notice', 'text', ['null' => true])
                ->addColumn('active', 'boolean', ['default' => false])
                ->addIndex('category_id')
                ->addIndex('time')
                ->addIndex('text', ['type' => 'fulltext'])
                ->addIndex('title', ['type' => 'fulltext'])
                ->create();
        }
    }
}
