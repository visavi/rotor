<?php

use Phinx\Migration\AbstractMigration;

class CreateBanhistTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('banhist', ['collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('send', 'string', ['limit' => 20])
            ->addColumn('type', 'boolean', ['default' => 0])
            ->addColumn('reason', 'text')
            ->addColumn('term', 'integer')
            ->addColumn('time', 'integer')
            ->addIndex('time')
            ->addIndex('user')
            ->create();
    }
}
