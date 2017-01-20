<?php

use Phinx\Migration\AbstractMigration;

class CreateSurpriseTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('surprise', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('user', 'string', ['limit' => 20])
            ->addColumn('year', 'year', ['limit' => 4])
            ->addColumn('time', 'integer')
            ->addIndex('user')
            ->create();
    }
}
