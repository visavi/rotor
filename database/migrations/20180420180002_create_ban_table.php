<?php

use Phinx\Migration\AbstractMigration;

class CreateBanTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        if (! $this->hasTable('ban')) {
            $table = $this->table('ban', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('user_id', 'integer', ['null' => true])
                ->addColumn('created_at', 'integer')
                ->addIndex('ip', ['unique' => true])
                ->create();
        }
    }
}
