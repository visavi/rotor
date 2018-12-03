<?php

use Phinx\Migration\AbstractMigration;

class CreateTest2Table extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('test2', ['collation' => env('DB_COLLATION')]);
        $table
            ->addColumn('user_id', 'integer')
            ->addColumn('text', 'text', ['null' => true])
            ->create();
    }
}
