<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserIdInGuestbooks extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('guestbooks');
        $table
            ->changeColumn('user_id', 'integer', ['null' => true])
            ->save();

        $this->execute('UPDATE guestbooks SET user_id=null WHERE user_id="0";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('UPDATE guestbooks SET user_id="0" WHERE ISNULL(user_id);');

        $table = $this->table('guestbooks');
        $table
            ->changeColumn('user_id', 'integer')
            ->save();
    }
}
