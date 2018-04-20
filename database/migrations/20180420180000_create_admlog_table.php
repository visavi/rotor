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
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('request', 'string', ['null' => true])
                ->addColumn('referer', 'string', ['null' => true])
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('created_at', 'integer')
                ->addIndex('created_at')
                ->create();
        }
    }
}
