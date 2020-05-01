<?php

use Phinx\Migration\AbstractMigration;

class CreateBlogsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('blogs')) {
            $table = $this->table('blogs', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('sort', 'integer', ['default' => 0])
                ->addColumn('parent_id', 'integer', ['default' => 0])
                ->addColumn('name', 'string', ['limit' => 100])
                ->addColumn('count_articles', 'integer', ['default' => 0])
                ->addColumn('closed', 'boolean', ['default' => 0])
                ->create();
        }
    }
}
