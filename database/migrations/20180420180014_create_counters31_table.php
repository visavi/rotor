<?php

use Phinx\Migration\AbstractMigration;

class CreateCounters31Table extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('counters31')) {
            $table = $this->table('counters31', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('period', 'datetime')
                ->addColumn('hosts', 'integer')
                ->addColumn('hits', 'integer')
                ->addIndex('period', ['unique' => true])
                ->create();
        }
    }
}
