<?php

use Phinx\Migration\AbstractMigration;

class CreateForumsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('forums')) {
            $table = $this->table('forums', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('sort', 'integer', ['default' => 0])
                ->addColumn('parent_id', 'integer', ['default' => 0])
                ->addColumn('title', 'string', ['limit' => 50])
                ->addColumn('description', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('last_topic_id', 'integer', ['default' => 0])
                ->addColumn('closed', 'boolean', ['default' => 0])
                ->addColumn('count_topics', 'integer', ['default' => 0])
                ->addColumn('count_posts', 'integer', ['default' => 0])
                ->create();
        }
    }
}
