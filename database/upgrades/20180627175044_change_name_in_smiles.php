<?php

use Phinx\Migration\AbstractMigration;

class ChangeNameInSmiles extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('smiles');
        $table->changeColumn('name', 'string', ['limit' => 100])
            ->save();

        $this->execute('UPDATE smiles SET name=concat("/uploads/smiles/", name);');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('smiles');
        $table->changeColumn('name', 'string', ['limit' => 25])
            ->save();

        $this->execute('UPDATE smiles SET name=replace(name, "/uploads/smiles/", "");');
    }
}
