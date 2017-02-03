<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateVoteTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('vote')) {
            $table = $this->table('vote', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('title', 'string', ['limit' => 100])
                ->addColumn('count', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
                ->addColumn('closed', 'boolean', ['default' => 0])
                ->addColumn('time', 'integer')
                ->create();
        }
    }
}
