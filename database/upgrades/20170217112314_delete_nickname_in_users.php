<?php

use Phinx\Migration\AbstractMigration;

class DeleteNicknameInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table->removeColumn('nickname')
            ->removeIndex('nickname')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table->addColumn('nickname', 'string', ['limit' => 20, 'null' => true])
            ->addIndex('nickname')
            ->save();
    }
}
