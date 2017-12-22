<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateVotepollTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('votepoll')) {
            $table = $this->table('votepoll', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('vote_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
                ->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('time', 'integer')
                ->create();
        }
    }
}
