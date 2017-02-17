<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInSpam extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('TRUNCATE spam');

        $table = $this->table('spam');
        $table
            ->changeColumn('relate', 'string', ['limit' => 20])
            ->changeColumn('user', 'integer')
            ->changeColumn('time', 'integer', ['null' => true])
            ->removeColumn('login')
            ->removeColumn('text')
            ->removeColumn('addtime')
            ->removeIndex('time')
            ->removeIndex('relate')
            ->save();

        $table->renameColumn('relate', 'relate_type');
        $table->renameColumn('idnum', 'relate_id');
        $table->renameColumn('user', 'user_id');
        $table->renameColumn('time', 'created_at');

        $table
            ->addIndex('created_at')
            ->addIndex(['relate_type', 'relate_id'], ['name' => 'relate_type'])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('TRUNCATE spam');

        $table = $this->table('spam');
        $table
            ->changeColumn('relate_type', 'integer')
            ->changeColumn('user_id', 'string', ['limit' => 20])
            ->changeColumn('created_at', 'integer')
            ->addColumn('login', 'string', ['limit' => 20])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('addtime', 'integer')
            ->removeIndex('created_at')
            ->removeIndexByName('relate_type')
            ->save();

        $table->renameColumn('relate_type', 'relate');
        $table->renameColumn('relate_id', 'idnum');
        $table->renameColumn('user_id', 'user');
        $table->renameColumn('created_at', 'time');

        $table
            ->addIndex('time')
            ->addIndex('relate')
            ->save();

    }
}
