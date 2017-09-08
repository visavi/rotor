<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreatePhotoTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('photo')) {
            $table = $this->table('photo', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('title', 'string', ['limit' => 50])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('link', 'string', ['limit' => 30])
                ->addColumn('time', 'integer')
                ->addColumn('rating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'default' => 0])
                ->addColumn('closed', 'boolean', ['default' => false])
                ->addColumn('comments', 'integer', ['signed' => false, 'default' => 0])
                ->addIndex('time')
                ->addIndex('user')
                ->create();
        }
    }
}
