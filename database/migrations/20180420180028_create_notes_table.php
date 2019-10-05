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
            $table = $this->table('notes', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
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
