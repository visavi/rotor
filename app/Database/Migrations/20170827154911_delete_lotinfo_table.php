<?php

use Phinx\Migration\AbstractMigration;

class DeleteLotinfoTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('lotinfo');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if (! $this->hasTable('lotinfo')) {
            $table = $this->table('lotinfo', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('date', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
                ->addColumn('sum', 'integer', ['signed' => false, 'default' => 0])
                ->addColumn('newnum', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
                ->addColumn('oldnum', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
                ->addColumn('winners', 'string', ['null' => true])
                ->create();
        }
    }
}
