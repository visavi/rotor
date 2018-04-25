<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateCounters24Table extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('counters24')) {
            $table = $this->table('counters24', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('hour', 'integer')
                ->addColumn('hosts', 'integer')
                ->addColumn('hits', 'integer')
                ->addIndex('hour', ['unique' => true])
                ->create();
        }
    }
}
