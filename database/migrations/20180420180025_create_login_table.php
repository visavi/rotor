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
            $table = $this->table('login', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('created_at', 'integer')
                ->addColumn('type', 'boolean', ['default' => 0])
                ->addIndex('user_id')
                ->addIndex('created_at')
                ->create();
        }
    }
}
