<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInCounter24 extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counter24');
        $table->removeIndexByName('count_hour');
        $table->addIndex('hour', ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('counter24');
        $table->removeIndexByName('hour');
        $table->addIndex('hour', ['unique' => true, 'name' => 'count_hour'])
            ->save();
    }
}
