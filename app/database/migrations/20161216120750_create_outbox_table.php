<?php

use Phinx\Migration\AbstractMigration;

class CreateOutboxTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('outbox', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('author', 'string', ['limit' => 20])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('time', 'integer')
            ->addIndex('time')
            ->addIndex('user')
            ->create();
    }
}
