<?php

use Phinx\Migration\AbstractMigration;

class RenameRekuserTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
        $table = $this->table('rekuser');
        $table->rename('adverts')->save();
    }
}
