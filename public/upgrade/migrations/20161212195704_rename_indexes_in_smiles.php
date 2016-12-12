<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInSmiles extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('smiles');
        $table->removeIndexByName('smiles_cats');
        $table->removeIndexByName('smiles_code');
        $table->addIndex('cats')
            ->addIndex('code')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('smiles');
        $table->removeIndexByName('cats');
        $table->removeIndexByName('code');
        $table->addIndex('cats', ['name' => 'smiles_cats'])
            ->addIndex('code', ['name' => 'smiles_code'])
            ->save();
    }
}
