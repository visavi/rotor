<?php

use Phinx\Migration\AbstractMigration;

class AddLangToUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function change()
    {
        $table = $this->table('users');
        $table->addColumn('lang', 'string', [
            'limit' => 2,
            'after' => 'city',
            'null' => true
        ])
            ->update();
    }
}
