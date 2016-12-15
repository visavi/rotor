<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInRules extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('rules');
        $users
            ->changeColumn('text', 'text', ['null' => true])
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
