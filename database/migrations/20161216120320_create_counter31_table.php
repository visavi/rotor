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
            $table
                ->addColumn('days', 'integer')
                ->addColumn('hosts', 'integer')
                ->addColumn('hits', 'integer')
                ->addIndex('days', ['unique' => true])
                ->create();
        }
    }
}
