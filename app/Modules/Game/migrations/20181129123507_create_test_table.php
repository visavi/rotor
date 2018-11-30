<?php

use Phinx\Migration\AbstractMigration;

class CreateTestTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('test', ['collation' => env('DB_COLLATION')]);
        $table
            ->addColumn('user_id', 'integer')
            ->addColumn('text', 'text', ['null' => true])
            ->create();
    }
}
