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
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('time', 'integer')
                ->addColumn('type', 'boolean', ['default' => false])
                ->addIndex('time')
                ->addIndex('user')
                ->create();
        }
    }
}
