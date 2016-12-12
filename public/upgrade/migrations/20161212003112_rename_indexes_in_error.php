<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInError extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('error');
        $table->removeIndexByName('error_num');
        $table->addIndex(['num', 'time'], ['name' => 'num_time'])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('error');
        $table->removeIndexByName('num_time');
        $table->addIndex(['num', 'time'], ['name' => 'error_num'])
            ->save();
    }
}
