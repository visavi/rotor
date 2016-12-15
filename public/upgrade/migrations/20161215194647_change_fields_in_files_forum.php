<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInFilesForum extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('files_forum');
        $users
            ->changeColumn('time', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
