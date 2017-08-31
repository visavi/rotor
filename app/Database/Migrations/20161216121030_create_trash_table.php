<?php

use Phinx\Migration\AbstractMigration;

class CreateTrashTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('trash')) {
            $table = $this->table('trash', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('author', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('time', 'integer')
                ->addColumn('del', 'integer')
                ->addIndex('time')
                ->addIndex('user')
                ->create();
        }
    }
}
