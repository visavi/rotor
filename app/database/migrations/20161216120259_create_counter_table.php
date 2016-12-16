<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateCounterTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('counter', ['collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('hours', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('days', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('allhosts', 'integer', ['signed' => false])
            ->addColumn('allhits', 'integer', ['signed' => false])
            ->addColumn('dayhosts', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('dayhits', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('hosts24', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->addColumn('hits24', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
            ->create();
    }
}
