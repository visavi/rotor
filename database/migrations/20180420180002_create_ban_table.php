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
            $table = $this->table('ban', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('ip', 'varbinary', ['limit' => 16])
                ->addColumn('user_id', 'integer', ['null' => true])
                ->addColumn('created_at', 'integer')
                ->addIndex('ip', ['unique' => true])
                ->create();
        }
    }
}
