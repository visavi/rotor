<?php

use Phinx\Migration\AbstractMigration;

class CreateTransfersTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('transfers', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('login', 'string', ['limit' => 20])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('summ', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('time', 'integer')
            ->addIndex('login')
            ->addIndex('time')
            ->addIndex('user')
            ->create();
    }
}
