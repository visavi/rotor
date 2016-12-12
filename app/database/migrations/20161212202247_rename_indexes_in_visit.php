<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInVisit extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('visit');
        $table->removeIndexByName('visit_nowtime');
        $table->removeIndexByName('visit_user');
        $table->addIndex('nowtime')
            ->addIndex('user', ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('visit');
        $table->removeIndexByName('nowtime');
        $table->removeIndexByName('user');
        $table->addIndex('nowtime', ['name' => 'visit_nowtime'])
            ->addIndex('user', ['unique' => true, 'name' => 'visit_user'])
            ->save();
    }
}
