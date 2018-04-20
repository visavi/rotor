<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateVoteanswerTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('voteanswer')) {
            $table = $this->table('voteanswer', ['collation' => env('DB_COLLATION')]);
            $table
                ->addColumn('vote_id', 'integer')
                ->addColumn('answer', 'string', ['limit' => 50])
                ->addColumn('result', 'integer', ['default' => 0])
                ->create();
        }
    }
}
