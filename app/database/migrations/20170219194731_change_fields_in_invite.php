<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInInvite extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('TRUNCATE invite');

        $table = $this->table('invite');
        $table
            ->changeColumn('user', 'integer')
            ->changeColumn('invited', 'integer', ['null' => true])
            ->changeColumn('time', 'integer', ['null' => true])
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('invited', 'invite_user_id');
        $table->renameColumn('time', 'created_at');

        $table
            ->removeIndexByName('time')
            ->removeIndexByName('user')
            ->addIndex('created_at')
            ->addIndex('user_id')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('TRUNCATE invite');

        $table = $this->table('invite');
        $table
            ->changeColumn('user_id', 'string', ['limit' => 20])
            ->changeColumn('invite_user_id', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('created_at', 'integer')
            ->save();

        $table->renameColumn('user_id', 'user');
        $table->renameColumn('invite_user_id', 'invited');
        $table->renameColumn('created_at', 'time');

        $table
            ->removeIndexByName('created_at')
            ->removeIndexByName('user_id')
            ->addIndex('time')
            ->addIndex('user')
            ->save();

    }
}
