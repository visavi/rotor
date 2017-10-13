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
            $table->addColumn('type', 'boolean')
                ->addColumn('value', 'string', ['limit' => 100])
                ->addColumn('user', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('time', 'integer')
                ->addIndex('type')
                ->addIndex('value')
                ->create();
        }
    }
}
