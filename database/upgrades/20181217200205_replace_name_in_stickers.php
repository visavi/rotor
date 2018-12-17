<?php

use Phinx\Migration\AbstractMigration;

class ReplaceNameInStickers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("UPDATE stickers SET name = replace(`name`, '/smiles/', '/stickers/')");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("UPDATE stickers SET name = replace(`name`, '/stickers/', '/smiles/')");
    }
}
