<?php

use Phinx\Migration\AbstractMigration;

class DeleteTotalbanInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table->removeColumn('totalban')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table->addColumn('totalban', 'boolean', ['signed' => false, 'default' => false])
            ->save();
    }
}
