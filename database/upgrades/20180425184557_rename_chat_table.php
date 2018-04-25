<?php

use Phinx\Migration\AbstractMigration;

class RenameChatTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('chat');
        $table->rename('chats');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('chats');
        $table->rename('chat');
    }
}
