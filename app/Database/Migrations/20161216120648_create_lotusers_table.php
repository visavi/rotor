<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateLotusersTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('lotusers')) {
            $table = $this->table('lotusers', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('num', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
                ->addColumn('time', 'integer')
                ->create();
        }
    }
}
