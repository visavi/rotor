<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInForums extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('forums');
        $users
            ->changeColumn('desc', 'string', ['limit' => 100, 'null' => true])
            ->changeColumn('last_themes', 'string', ['limit' => 50, 'null' => true])
            ->changeColumn('last_user', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('closed', 'boolean', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
