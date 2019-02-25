<?php

use Phinx\Migration\AbstractMigration;

class DeleteIndexInOnline extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('online');
        $table
            ->removeIndexByName('ip')
            ->removeIndexByName('updated_at')
            ->removeIndexByName('user_id')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('online');
        $table
            ->addIndex('ip')
            ->addIndex('updated_at')
            ->addIndex('user_id')
            ->save();
    }
}
