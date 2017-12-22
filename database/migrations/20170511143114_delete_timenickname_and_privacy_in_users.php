<?php

use Phinx\Migration\AbstractMigration;

class DeleteTimenicknameAndPrivacyInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table->removeColumn('timenickname')
            ->removeColumn('privacy')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table->addColumn('timenickname', 'integer', ['default' => 0])
            ->addColumn('privacy', 'boolean', ['default' => false])
            ->save();
    }
}
