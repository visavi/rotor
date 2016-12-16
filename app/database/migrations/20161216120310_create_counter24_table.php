<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateCounter24Table extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('counter24', ['collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('hour', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('hosts', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('hits', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addIndex('hour', ['unique' => true])
            ->create();

    }
}
