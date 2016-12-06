<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class DeleteFieldsInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table->removeColumn('postguest')
            ->removeColumn('postnews')
            ->removeColumn('postforum')
            ->removeColumn('themesforum')
            ->removeColumn('postboard')
            ->removeColumn('postprivat')
            ->removeColumn('navigation')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table->addColumn('postguest', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'default' => 0])
            ->addColumn('postnews', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'default' => 0])
            ->addColumn('postforum', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'default' => 0])
            ->addColumn('themesforum', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'default' => 0])
            ->addColumn('postboard', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'default' => 0])
            ->addColumn('postprivat', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'default' => 0])
            ->addColumn('navigation', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => 0])
            ->save();
    }
}
