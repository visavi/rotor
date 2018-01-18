<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddParentIdToCatsblog extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('catsblog');
        $table->addColumn('parent_id', 'integer', [
            'limit'   => MysqlAdapter::INT_SMALL,
            'default' => 0,
            'after'   => 'sort',
            'signed'  => false,
        ])
            ->addColumn('closed', 'boolean', [
                'default' => false,
            ])
            ->update();
    }
}
