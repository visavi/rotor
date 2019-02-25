<?php

use Phinx\Migration\AbstractMigration;

class ChangeIndexInLogin extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('login');
        $table
            ->removeIndexByName('created_at')
            ->removeIndexByName('user_id')
            ->addIndex(['user_id', 'created_at'], ['name' => 'user_time'])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('login');
        $table
            ->removeIndexByName('user_time')
            ->addIndex('created_at')
            ->addIndex('user_id')
            ->save();
    }
}
