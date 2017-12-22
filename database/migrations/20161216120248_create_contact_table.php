<?php

use Phinx\Migration\AbstractMigration;

class CreateContactTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('contact')) {
            $table = $this->table('contact', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('name', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('time', 'integer')
                ->addIndex('time')
                ->addIndex('user')
                ->create();
        }
    }
}
