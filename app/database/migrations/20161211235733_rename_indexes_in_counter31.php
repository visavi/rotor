<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInCounter31 extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counter31');
        $table->removeIndexByName('count_days');
        $table->addIndex('days', ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('counter31');
        $table->removeIndexByName('days');
        $table->addIndex('days', ['unique' => true, 'name' => 'count_days'])
            ->save();
    }
}
