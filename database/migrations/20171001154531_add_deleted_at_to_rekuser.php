<?php

use Phinx\Migration\AbstractMigration;

class AddDeletedAtToRekuser extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function change()
    {
        $table = $this->table('rekuser');
        $table->addColumn('deleted_at', 'integer', [
            'null' => true
        ])
            ->update();
    }
}
