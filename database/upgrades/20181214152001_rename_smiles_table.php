<?php

use Phinx\Migration\AbstractMigration;

class RenameSmilesTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
        $table = $this->table('smiles');
        $table->rename('stickers')->save();
    }
}
