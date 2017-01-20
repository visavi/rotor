<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateErrorTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('error', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('num', 'integer', ['limit' => MysqlAdapter::INT_SMALL])
            ->addColumn('request', 'string', ['null' => true])
            ->addColumn('referer', 'string', ['null' => true])
            ->addColumn('username', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('ip', 'string', ['limit' => 15])
            ->addColumn('brow', 'string', ['limit' => 25])
            ->addColumn('time', 'integer')
            ->addIndex(['num', 'time'], ['name' => 'num_time'])
            ->create();
    }
}
