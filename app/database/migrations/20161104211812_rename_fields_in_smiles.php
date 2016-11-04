<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInSmiles extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('smiles');
        $table->renameColumn('smiles_id', 'id');
        $table->renameColumn('smiles_cats', 'cats');
        $table->renameColumn('smiles_name', 'name');
        $table->renameColumn('smiles_code', 'code');
    }
}
