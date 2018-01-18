<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class RenameVotesInOffers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('offers');
        $table
            ->renameColumn('votes', 'rating')
            ->changeColumn('rating', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'default' => 0])
            ->save();

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('offers');

        $table
            ->renameColumn('rating', 'votes')
            ->changeColumn('votes', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => 0])
            ->save();
    }
}
