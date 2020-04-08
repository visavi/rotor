<?php

use Phinx\Migration\AbstractMigration;

class CreateLoginTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('login')) {
            $table = $this->table('login', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('ip', 'varbinary', ['limit' => 16])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('created_at', 'integer')
                ->addColumn('type', 'boolean', ['default' => 0])
                ->addIndex(['user_id', 'created_at'], ['name' => 'user_time'])
                ->create();
        }
    }
}
