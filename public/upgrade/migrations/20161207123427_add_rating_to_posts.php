<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddRatingToPosts extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('posts');
        $table->addColumn('rating', 'integer', [
            'after' => 'text',
            'limit' => MysqlAdapter::INT_SMALL,
            'default' => 0
        ])
            ->update();
    }
}
