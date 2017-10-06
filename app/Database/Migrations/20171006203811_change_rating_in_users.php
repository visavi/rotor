<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ChangeRatingInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table
            ->changeColumn('posrating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'default' => 0])
            ->changeColumn('negrating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table
            ->changeColumn('posrating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
            ->changeColumn('negrating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
            ->save();
    }
}
