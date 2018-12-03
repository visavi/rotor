<?php

use Phinx\Migration\AbstractMigration;

class CreateTest3Table extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('test3', ['collation' => env('DB_COLLATION')]);
        $table
            ->addColumn('user_id', 'integer')
            ->addColumn('text', 'text', ['null' => true])
            ->create();
    }
}
