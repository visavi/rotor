<?php

use Phinx\Migration\AbstractMigration;

class CreateSocialsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('socials')) {
            $table = $this->table('socials', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('network', 'string')
                ->addColumn('uid', 'string')
                ->addIndex('user_id')
                ->create();
        }
    }
}
