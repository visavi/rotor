<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateEventsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('events', ['collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('title', 'string', ['limit' => 100])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('author', 'string', ['limit' => 20])
            ->addColumn('image', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('time', 'integer')
            ->addColumn('comments', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
            ->addColumn('closed', 'boolean', ['signed' => false, 'default' => 0])
            ->addColumn('top', 'boolean', ['default' => 0])
            ->addIndex('time')
            ->create();
    }
}
