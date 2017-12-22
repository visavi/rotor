<?php

use Phinx\Migration\AbstractMigration;

class CreateNotebookTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('notebook')) {
            $table = $this->table('notebook', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('time', 'integer')
                ->addIndex('user', ['unique' => true])
                ->create();
        }
    }
}
