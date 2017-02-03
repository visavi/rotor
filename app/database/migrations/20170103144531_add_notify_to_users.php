<?php

use Phinx\Migration\AbstractMigration;

class AddNotifyToUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function change()
    {
        $table = $this->table('users');
        $table->addColumn('notify', 'boolean', [
            'after' => 'privacy',
            'default' => true
        ])
            ->update();
    }
}
