<?php

use Phinx\Migration\AbstractMigration;

class CreateNotesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('notes')) {
            $table = $this->table('notes', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('edit_user_id', 'integer')
                ->addColumn('updated_at', 'integer')
                ->addIndex('user_id', ['unique' => true])
                ->create();
        }
    }
}
