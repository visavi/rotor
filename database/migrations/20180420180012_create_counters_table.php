<?php

use Phinx\Migration\AbstractMigration;

class CreateCountersTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('counters')) {
            $table = $this->table('counters', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('period', 'datetime')
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
