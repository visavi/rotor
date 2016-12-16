<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateSmilesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('smiles', ['collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('cats', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('code', 'string', ['limit' => 20])
            ->addIndex('cats')
            ->addIndex('code')
            ->create();
    }
}
