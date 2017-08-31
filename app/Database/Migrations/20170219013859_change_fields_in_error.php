<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInError extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('TRUNCATE error');

        $table = $this->table('error');
        $table
            ->changeColumn('username', 'integer')
            ->changeColumn('time', 'integer', ['null' => true])
            ->removeIndexByName('num_time')
            ->save();

        $table->renameColumn('num', 'code');
        $table->renameColumn('username', 'user_id');
        $table->renameColumn('time', 'created_at');

        $table
            ->addIndex(['code', 'created_at'], ['name' => 'code'])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('TRUNCATE error');

        $table = $this->table('error');
        $table
            ->changeColumn('user_id', 'string', ['limit' => 20])
            ->changeColumn('created_at', 'integer')
            ->removeIndexByName('code')
            ->save();

        $table->renameColumn('code', 'num');
        $table->renameColumn('user_id', 'username');
        $table->renameColumn('created_at', 'time');

        $table
            ->addIndex(['num', 'time'], ['name' => 'num_time'])
            ->save();

    }
}
