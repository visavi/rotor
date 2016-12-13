<?php

use Phinx\Migration\AbstractMigration;

class CreateBlacklistTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('blacklist2', ['collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('type', 'boolean')
            ->addColumn('value', 'string', ['limit' => 100])
            ->addColumn('user', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('time', 'integer')
            ->addIndex('type')
            ->addIndex('value')
            ->create();
    }
}
