<?php

use Phinx\Migration\AbstractMigration;

class CreateDownsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('downs')) {
            $table = $this->table('downs', ['engine' => config('DB_ENGINE'), 'collation' => config('DB_COLLATION')]);
            $table
                ->addColumn('category_id', 'integer')
                ->addColumn('title', 'string', ['limit' => 100])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('user_id', 'integer')
                ->addColumn('created_at', 'integer')
                ->addColumn('count_comments', 'integer', ['default' => 0])
                ->addColumn('rating', 'integer', ['default' => 0])
                ->addColumn('rated', 'integer', ['default' => 0])
                ->addColumn('loads', 'integer', ['default' => 0])
                ->addColumn('active', 'boolean', ['default' => 0])
                ->addColumn('updated_at', 'integer', ['null' => true])
                ->addIndex('category_id')
                ->addIndex('created_at');

            $mysql = $this->query('SHOW VARIABLES LIKE "version"')->fetch();

            if (config('DB_ENGINE') === 'MyISAM' || version_compare($mysql['Value'], '5.6.0', '>=')) {
                $table->addIndex('text', ['type' => 'fulltext']);
                $table->addIndex('title', ['type' => 'fulltext']);
            }

            $table->create();
        }
    }
}
