<?php

use Phinx\Migration\AbstractMigration;

class CreateBankTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        if (! $this->hasTable('bank')) {
            $table = $this->table('bank', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('sum', 'integer', ['signed' => false, 'default' => 0])
                ->addColumn('oper', 'integer', ['signed' => false, 'default' => 0])
                ->addColumn('time', 'integer')
                ->addIndex('user', ['unique' => true])
                ->create();
        }
    }
}
