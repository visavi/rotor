<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateBlogsTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('blogs', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('category_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('title', 'string', ['limit' => 50])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('tags', 'string', ['limit' => 100])
            ->addColumn('rating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'default' => 0])
            ->addColumn('visits', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('comments', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
            ->addColumn('time', 'integer')
            ->addIndex('category_id')
            ->addIndex('time')
            ->addIndex('user')
            ->create();
    }
}
