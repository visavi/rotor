<?php

use Phinx\Migration\AbstractMigration;

class CreateBookmarksTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('bookmarks')) {
            $table = $this->table('bookmarks', ['engine' => env('DB_ENGINE'), 'collation' => env('DB_COLLATION')]);
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
