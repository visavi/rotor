<?php

use Phinx\Migration\AbstractMigration;

class CreateNoticesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('notices')) {
            $table = $this->table('notices', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('type', 'string', ['limit' => 20])
                ->addColumn('name', 'string', ['limit' => 100])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('user_id', 'integer')
                ->addColumn('created_at', 'integer')
                ->addColumn('updated_at', 'integer')
                ->addColumn('protect', 'boolean', ['default' => 0])
                ->addIndex('type', ['unique' => true])
                ->create();
        }
    }
}
