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
            $table = $this->table('notebook', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('time', 'integer')
                ->addIndex('user', ['unique' => true])
                ->create();
        }
    }
}
