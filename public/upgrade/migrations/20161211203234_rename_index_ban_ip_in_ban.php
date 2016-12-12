<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexBanIpInBan extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('ban');
        $table->removeIndexByName('ban_ip');
        $table->addIndex('ip', ['unique' => true])->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('ban');
        $table->removeIndexByName('ip');
        $table->addIndex('ip', ['unique' => true, 'name' => 'ban_ip'])->save();
    }
}
