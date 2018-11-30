<?php

use Phinx\Migration\AbstractMigration;

class CreatePostsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('posts')) {
            $table = $this->table('posts', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('topic_id', 'integer')
                ->addColumn('user_id', 'integer')
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('rating', 'integer', ['default' => 0])
                ->addColumn('created_at', 'integer')
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('brow', 'string', ['limit' => 25])
                ->addColumn('edit_user_id', 'integer', ['null' => true])
                ->addColumn('updated_at', 'integer', ['null' => true])
                ->addIndex(['topic_id', 'created_at'], ['name' => 'topic_time'])
                ->addIndex('user_id');

            $mysql = $this->query('SHOW VARIABLES LIKE "version"')->fetch();

            if(version_compare($mysql['Value'], '5.6.0', '>=')) {
                $table->addIndex('text', ['type' => 'fulltext']);
            }

            $table->create();
        }
    }
}
