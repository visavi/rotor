<?php

use Phinx\Migration\AbstractMigration;

class CreateTransfersTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('transfers')) {
            $table = $this->table('transfers', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('login', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('summ', 'integer', ['signed' => false, 'default' => 0])
                ->addColumn('time', 'integer')
                ->addIndex('login')
                ->addIndex('time')
                ->addIndex('user')
                ->create();
        }
    }
}
