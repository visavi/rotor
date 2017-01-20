<?php

use Phinx\Migration\AbstractMigration;

class CreateLoginTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('login', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('ip', 'string', ['limit' => 15])
            ->addColumn('brow', 'string', ['limit' => 25])
            ->addColumn('time', 'integer')
            ->addColumn('type', 'boolean', ['default' => 0])
            ->addIndex('time')
            ->addIndex('user')
            ->create();
    }
}
