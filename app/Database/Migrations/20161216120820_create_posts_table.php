<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreatePostsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('posts')) {
            $table = $this->table('posts', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('forum_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
                ->addColumn('topic_id', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
                ->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('rating', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'default' => 0])
                ->addColumn('time', 'integer')
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('edit', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('edit_time', 'integer', ['default' => 0])
                ->addIndex('forum_id')
                ->addIndex(['topic_id', 'time'], ['name' => 'topic_time'])
                ->addIndex('user')
                ->addIndex('text', ['type' => 'fulltext'])
                ->create();
        }
    }
}
