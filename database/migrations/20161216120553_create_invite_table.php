<?php

use Phinx\Migration\AbstractMigration;

class CreateInviteTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('invite')) {
            $table = $this->table('invite', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('hash', 'string', ['limit' => 15])
                ->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('invited', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('used', 'boolean', ['default' =>false])
                ->addColumn('time', 'integer')
                ->addIndex('user')
                ->addIndex('used')
                ->addIndex('time')
                ->create();
        }
    }
}
