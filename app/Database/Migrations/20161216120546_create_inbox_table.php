<?php

use Phinx\Migration\AbstractMigration;

class CreateInboxTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('inbox')) {
            $table = $this->table('inbox', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('author', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('time', 'integer')
                ->addIndex('time')
                ->addIndex('user')
                ->create();
        }
    }
}
