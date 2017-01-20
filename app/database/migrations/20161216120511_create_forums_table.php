<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateForumsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('forums', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('sort', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
            ->addColumn('parent', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
            ->addColumn('title', 'string', ['limit' => 50])
            ->addColumn('desc', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('topics', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('posts', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('last_id', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('last_themes', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('last_user', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('last_time', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('closed', 'boolean', ['default' => 0])
            ->create();
    }
}
