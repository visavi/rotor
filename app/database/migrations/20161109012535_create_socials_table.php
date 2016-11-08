<?php

use Phinx\Migration\AbstractMigration;

class CreateSocialsTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('socials');
        $table->addColumn('user', 'string', ['limit' => 128])
            ->addColumn('network', 'string')
            ->addColumn('uid', 'string')
            ->addIndex('user')
            ->create();
    }
}
