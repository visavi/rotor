<?php

use Phinx\Migration\AbstractMigration;

class CreateVisitTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('visit')) {
            $table = $this->table('visit', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('self', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('page', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('ip', 'string', ['limit' => 15])
                ->addColumn('count', 'integer', ['signed' => false, 'default' => 0])
                ->addColumn('nowtime', 'integer', ['default' => 0])
                ->addIndex('user', ['unique' => true])
                ->addIndex('nowtime')
                ->create();
        }
    }
}
