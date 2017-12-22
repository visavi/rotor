<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateCounter31Table extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('counter31')) {
            $table = $this->table('counter31', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('days', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
                ->addColumn('hosts', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
                ->addColumn('hits', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
                ->addIndex('days', ['unique' => true])
                ->create();
        }
    }
}
