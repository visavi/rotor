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
            $table = $this->table('ban', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('user', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('time', 'integer')
                ->addIndex('ip', ['unique' => true])
                ->create();
        }
    }
}
