<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateBlogsTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        if (! $this->hasTable('blogs')) {
            $table = $this->table('blogs', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('category_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
                ->addColumn('user_id', 'integer')
                ->addColumn('title', 'string', ['limit' => 50])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('tags', 'string', ['limit' => 100])
                ->addColumn('rating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'default' => 0])
                ->addColumn('visits', 'integer', ['signed' => false, 'default' => 0])
                ->addColumn('count_comments', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
                ->addColumn('created_at', 'integer')
                ->addIndex('category_id')
                ->addIndex('user_id')
                ->addIndex('created_at')
                ->create();
        }
    }
}
