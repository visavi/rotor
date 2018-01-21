<?php

use Phinx\Migration\AbstractMigration;

class ChangeEditUserIdInChat extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('chat');
        $table->changeColumn('edit_user_id', 'integer', ['null' => true])
            ->changeColumn('created_at', 'integer', ['null' => true])
            ->changeColumn('updated_at', 'integer', ['null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('chat');
        $table
            ->changeColumn('edit_user_id', 'integer')
            ->changeColumn('created_at', 'integer')
            ->changeColumn('updated_at', 'integer')
            ->save();
    }
}
