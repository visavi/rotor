<?php

use Phinx\Migration\AbstractMigration;

class CreateFloodsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('floods')) {
            $table = $this->table('floods', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer', ['null' => true])
                ->addColumn('page', 'string', ['limit' => 30])
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id')
                ->create();
        }
    }
}
