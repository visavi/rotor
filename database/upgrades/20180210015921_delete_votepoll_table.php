<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class DeleteVotepollTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->dropTable('votepoll');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if (! $this->hasTable('votepoll')) {
            $table = $this->table('votepoll', ['collation' => env('DB_COLLATION')]);
            $table->addColumn('vote_id', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'signed' => false])
                ->addColumn('user_id', 'integer')
                ->addColumn('created_at', 'integer')
                ->create();
        }
    }
}
