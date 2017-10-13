<?php

use Phinx\Migration\AbstractMigration;

class CreateNoteTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('note')) {
            $table = $this->table('note', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('edit', 'string', ['limit' => 20])
                ->addColumn('time', 'integer')
                ->addIndex('user', ['unique' => true])
                ->create();
        }
    }
}
