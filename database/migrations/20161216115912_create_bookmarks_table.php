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
            $table
                ->addColumn('user_id', 'integer')
                ->addColumn('topic_id', 'integer')
                ->addColumn('count_posts', 'integer')
                ->addIndex('topic_id')
                ->addIndex('user_id')
                ->create();
        }
    }
}
