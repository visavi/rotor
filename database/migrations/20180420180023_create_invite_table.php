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
            $table
                ->addColumn('hash', 'string', ['limit' => 15])
                ->addColumn('user_id', 'integer')
                ->addColumn('invite_user_id', 'integer', ['null' => true])
                ->addColumn('used', 'boolean', ['default' => 0])
                ->addColumn('created_at', 'integer')
                ->addIndex('used')
                ->addIndex('created_at')
                ->addIndex('user_id')
                ->create();
        }
    }
}
