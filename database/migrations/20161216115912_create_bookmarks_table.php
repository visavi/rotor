<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateBookmarksTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('bookmarks')) {
            $table = $this->table('bookmarks', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('topic_id', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
                ->addColumn('forum_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
                ->addColumn('posts', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false])
                ->addIndex('forum_id')
                ->addIndex('topic_id')
                ->addIndex('user')
                ->create();
        }
    }
}
