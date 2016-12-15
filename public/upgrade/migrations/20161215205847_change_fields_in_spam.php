<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInSpam extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('spam');
        $users
            ->changeColumn('relate', 'boolean')
            ->changeColumn('text', 'text', ['null' => true])
            ->changeColumn('time', 'integer')
            ->changeColumn('addtime', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
