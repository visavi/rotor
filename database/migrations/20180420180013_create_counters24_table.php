<?php

use Phinx\Migration\AbstractMigration;

class CreateCounters24Table extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('counters24')) {
            $table = $this->table('counters24', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('period', 'datetime')
                ->addColumn('hosts', 'integer')
                ->addColumn('hits', 'integer')
                ->addIndex('period', ['unique' => true])
                ->create();
        }
    }
}
