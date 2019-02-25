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
            $table = $this->table('counters24', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('period', 'datetime')
                ->addColumn('hosts', 'integer')
                ->addColumn('hits', 'integer')
                ->addIndex('period', ['unique' => true])
                ->create();
        }
    }
}
