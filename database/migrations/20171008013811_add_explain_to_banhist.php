<?php

use Phinx\Migration\AbstractMigration;

class AddExplainToBanhist extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function change()
    {
        $table = $this->table('banhist');
        $table->addColumn('explain', 'boolean', ['default' => false])
            ->update();
    }
}
