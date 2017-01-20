<?php

use Phinx\Migration\AbstractMigration;

class CreateRulesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('rules', ['engine' => 'MyISAM',  'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('text', 'text', ['null' => true])
            ->addColumn('time', 'integer')
            ->create();
    }
}
