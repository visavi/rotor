<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateCatsblogTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('catsblog')) {
            $table = $this->table('catsblog', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('sort', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false, 'default' => 0])
                ->addColumn('name', 'string', ['limit' => 100])
                ->addColumn('count', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
                ->create();
        }
    }
}
