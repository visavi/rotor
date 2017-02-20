<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInOnline extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('TRUNCATE online');

        $table = $this->table('online');
        $table
            ->changeColumn('user', 'integer', ['null' => true])
            ->changeColumn('time', 'integer', ['null' => true])
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('time', 'updated_at');

        $table
            ->removeIndexByName('time')
            ->removeIndexByName('user')
            ->addIndex('updated_at')
            ->addIndex('user_id')
            ->save();

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('TRUNCATE online');

        $table = $this->table('online');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('updated_at', 'time')
            ->save();

        $table
            ->removeIndexByName('updated_at')
            ->removeIndexByName('user_id')
            ->addIndex('time')
            ->addIndex('user')
            ->save();
    }
}
