<?php

use Phinx\Migration\AbstractMigration;

class CreateSpamTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('spam')) {
            $table = $this->table('spam', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('relate', 'boolean')
                ->addColumn('idnum', 'integer', ['signed' => false])
                ->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('login', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('time', 'integer')
                ->addColumn('addtime', 'integer')
                ->addColumn('link', 'string', ['limit' => 100])
                ->addIndex('relate')
                ->addIndex('time')
                ->create();
        }
    }
}
