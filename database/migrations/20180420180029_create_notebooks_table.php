<?php

use Phinx\Migration\AbstractMigration;

class CreateNotebooksTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('notebooks')) {
            $table = $this->table('notebooks', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('created_at', 'integer')
                ->addIndex('user_id', ['unique' => true])
                ->create();
        }
    }
}
