<?php

use Phinx\Migration\AbstractMigration;

class DeleteConfirmregAndBanInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table->removeColumn('confirmreg')
            ->removeColumn('ban')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table->addColumn('confirmregkey', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('ban', 'boolean', ['signed' => false, 'default' => false])
            ->save();
    }
}
