<?php

use Phinx\Migration\AbstractMigration;

class CreateNoteTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('note', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('edit', 'string', ['limit' => 20])
            ->addColumn('time', 'integer')
            ->addIndex('user', ['unique' => true])
            ->create();
    }
}
