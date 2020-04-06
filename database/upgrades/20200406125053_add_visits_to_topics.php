<?php

use Phinx\Migration\AbstractMigration;

class AddVisitsToTopics extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change(): void
    {
        $table = $this->table('topics');
        $table->addColumn('visits', 'integer', ['default' => 0, 'after' => 'count_posts'])
            ->update();
    }
}
