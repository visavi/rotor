<?php

use Phinx\Migration\AbstractMigration;

class DeleteIcqAndSkypeInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table->removeColumn('icq')
            ->removeColumn('skype')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table->addColumn('icq', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('skype', 'string', ['limit' => 32, 'null' => true])
            ->save();
    }
}
