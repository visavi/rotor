<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInVisit extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('visit');
        $users
            ->changeColumn('self', 'string', ['limit' => 100, 'null' => true])
            ->changeColumn('page', 'string', ['limit' => 100, 'null' => true])
            ->changeColumn('ip', 'string', ['limit' => 15])
            ->changeColumn('nowtime', 'integer', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
