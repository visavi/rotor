<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ChangeFieldsInError extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('error');
        $users
            ->changeColumn('num', 'integer', ['limit' => MysqlAdapter::INT_SMALL])
            ->changeColumn('request', 'string', ['null' => true])
            ->changeColumn('referer', 'string', ['null' => true])
            ->changeColumn('username', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('ip', 'string', ['limit' => 15])
            ->changeColumn('brow', 'string', ['limit' => 25])
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
