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
        if (! $this->hasTable('counter')) {
            $table = $this->table('counter', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('hours', 'integer')
                ->addColumn('days', 'integer')
                ->addColumn('allhosts', 'integer')
                ->addColumn('allhits', 'integer')
                ->addColumn('dayhosts', 'integer')
                ->addColumn('dayhits', 'integer')
                ->addColumn('hosts24', 'integer')
                ->addColumn('hits24', 'integer')
                ->create();
        }
    }
}
