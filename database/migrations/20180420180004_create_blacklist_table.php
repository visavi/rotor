<?php

use Phinx\Migration\AbstractMigration;

class CreateBlacklistTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        if (! $this->hasTable('blacklist')) {
            $table = $this->table('blacklist', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('type', 'string', ['limit' => 30])
                ->addColumn('value', 'string', ['limit' => 100])
                ->addColumn('user_id', 'integer')
                ->addColumn('created_at', 'integer')
                ->addIndex('type')
                ->addIndex('value')
                ->create();
        }
    }
}
