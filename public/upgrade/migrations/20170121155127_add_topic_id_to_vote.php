<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddTopicIdToVote extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('vote');
        $table->addColumn('topic_id', 'integer', [
            'limit' => MysqlAdapter::INT_MEDIUM,
            'default' => 0
        ])
            ->update();
    }
}
