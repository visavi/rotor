<?php

use Phinx\Migration\AbstractMigration;

class DeleteIpbindingInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table->removeColumn('ipbinding')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table->addColumn('ipbinding', 'boolean', ['default' => 0])
            ->save();
    }
}
