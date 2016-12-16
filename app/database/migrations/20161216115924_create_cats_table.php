<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateCatsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('cats', ['collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('sort', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
            ->addColumn('parent', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('count', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
            ->addColumn('folder', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('closed', 'boolean', ['default' => 0])
            ->create();
    }
}
