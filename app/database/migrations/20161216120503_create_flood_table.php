<?php

use Phinx\Migration\AbstractMigration;

class CreateFloodTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('flood', ['collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('page', 'string', ['limit' => 30])
            ->addColumn('time', 'integer')
            ->addIndex('user')
            ->create();
    }
}
