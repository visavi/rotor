<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateTopicsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('topics')) {
            $table = $this->table('topics', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('forum_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
                ->addColumn('title', 'string', ['limit' => 50])
                ->addColumn('author', 'string', ['limit' => 20])
                ->addColumn('closed', 'boolean', ['default' => false])
                ->addColumn('locked', 'boolean', ['default' => false])
                ->addColumn('posts', 'integer', ['signed' => false, 'default' => 0])
                ->addColumn('last_user', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('last_time', 'integer', ['default' => 0])
                ->addColumn('moderators', 'string', ['null' => true])
                ->addColumn('note', 'string', ['null' => true])
                ->addIndex('forum_id')
                ->addIndex('last_time')
                ->addIndex('locked');

            $mysql = $this->query('SHOW VARIABLES LIKE "version"')->fetch();

            if(version_compare($mysql['Value'], '5.6.0', '>=')) {
                $table->addIndex('title', ['type' => 'fulltext']);
            }

            $table->create();
        }
    }
}
