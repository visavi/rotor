<?php

use Phinx\Migration\AbstractMigration;

class CreateAntimatTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('antimat', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('string', 'string', ['limit' => 100])
            ->create();
    }
}
