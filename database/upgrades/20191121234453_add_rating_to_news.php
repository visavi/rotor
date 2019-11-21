<?php

use Phinx\Migration\AbstractMigration;

class AddRatingToNews extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change(): void
    {
        $table = $this->table('news');
        $table->addColumn('rating', 'integer', ['default' => 0, 'after' => 'top'])
            ->update();
    }
}
