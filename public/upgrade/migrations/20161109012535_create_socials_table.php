<?php

use Phinx\Migration\AbstractMigration;

class CreateSocialsTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('socials', ['collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('network', 'string')
            ->addColumn('uid', 'string')
            ->addIndex('user')
            ->create();
    }
}
