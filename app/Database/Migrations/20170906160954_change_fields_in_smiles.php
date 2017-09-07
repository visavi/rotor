<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInSmiles extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('smiles');

        $table->removeColumn('cats');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('smiles');

        $table->addColumn('cats', 'integer', ['default' => 0]);

        $table->addIndex('cats')
            ->save();
    }
}
