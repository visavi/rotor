<?php

use Phinx\Migration\AbstractMigration;

class AddPhoneToUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function change()
    {
        $table = $this->table('users');
        $table->addColumn('phone', 'string', ['limit' => 15, 'after' => 'site', 'null' => true])
            ->update();
    }
}
