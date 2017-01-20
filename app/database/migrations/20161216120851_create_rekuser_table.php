<?php

use Phinx\Migration\AbstractMigration;

class CreateRekuserTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('rekuser', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('site', 'string', ['limit' => 50])
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('color', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('bold', 'boolean', ['default' => 0])
            ->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('time', 'integer')
            ->create();
    }
}
