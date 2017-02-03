<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateFilesForumTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('files_forum')) {
            $table = $this->table('files_forum', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('topic_id', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
                ->addColumn('post_id', 'integer', ['signed' => false])
                ->addColumn('hash', 'string', ['limit' => 40])
                ->addColumn('name', 'string', ['limit' => 60])
                ->addColumn('size', 'integer', ['signed' => false])
                ->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('time', 'integer')
                ->addIndex('topic_id')
                ->addIndex('post_id')
                ->addIndex('user')
                ->addIndex('time')
                ->create();
        }
    }
}
