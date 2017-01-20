<?php

use Phinx\Migration\AbstractMigration;

class CreateInviteTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('invite', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('hash', 'string', ['limit' => 15])
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('invited', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('used', 'boolean', ['default' => 0])
            ->addColumn('time', 'integer')
            ->addIndex('user')
            ->addIndex('used')
            ->addIndex('time')
            ->create();
    }
}
