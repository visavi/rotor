<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateOffersTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('offers')) {
            $table = $this->table('offers', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('type', 'boolean', ['default' => false])
                ->addColumn('title', 'string', ['limit' => 50])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('votes', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
                ->addColumn('time', 'integer')
                ->addColumn('status', 'boolean', ['default' => false])
                ->addColumn('comments', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
                ->addColumn('closed', 'boolean', ['default' => false])
                ->addColumn('text_reply', 'text', ['null' => true])
                ->addColumn('user_reply', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('time_reply', 'integer', ['default' => 0])
                ->addIndex('time')
                ->addIndex('votes')
                ->create();
        }
    }
}
