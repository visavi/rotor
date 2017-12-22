<?php

use Phinx\Migration\AbstractMigration;

class CreateBanhistTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        if (! $this->hasTable('banhist')) {
            $table = $this->table('banhist', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('send', 'string', ['limit' => 20])
                ->addColumn('type', 'boolean', ['default' => false])
                ->addColumn('reason', 'text', ['null' => true])
                ->addColumn('term', 'integer', ['default' => 0])
                ->addColumn('time', 'integer')
                ->addIndex('time')
                ->addIndex('user')
                ->create();
        }
    }
}
