<?php

use Phinx\Migration\AbstractMigration;

class CreateSocialsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('socials', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('user', 'string', ['limit' => 128])
            ->addColumn('network', 'string')
            ->addColumn('uid', 'string')
            ->addIndex('user')
            ->create();
    }
}
