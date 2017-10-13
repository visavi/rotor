<?php

use Phinx\Migration\AbstractMigration;

class CreateAdmlogTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        if (! $this->hasTable('admlog')) {
            $table = $this->table('admlog', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('request', 'string', ['null' => true])
                ->addColumn('referer', 'string', ['null' => true])
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('time', 'integer')
                ->create();
        }
    }
}
