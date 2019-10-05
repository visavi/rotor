<?php

use Phinx\Migration\AbstractMigration;

class CreateLoadsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('loads')) {
            $table = $this->table('loads', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('sort', 'integer', ['default' => 0])
                ->addColumn('parent_id', 'integer', ['default' => 0])
                ->addColumn('name', 'string', ['limit' => 100])
                ->addColumn('count_downs', 'integer', ['default' => 0])
                ->addColumn('closed', 'boolean', ['default' => 0])
                ->create();
        }
    }
}
