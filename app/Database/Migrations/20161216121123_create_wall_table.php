<?php

use Phinx\Migration\AbstractMigration;

class CreateWallTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('wall')) {
            $table = $this->table('wall', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('login', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('time', 'integer')
                ->addIndex('user')
                ->create();
        }
    }
}
