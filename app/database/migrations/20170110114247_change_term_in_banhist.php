<?php

use Phinx\Migration\AbstractMigration;

class ChangeTermInBanhist extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('banhist');
        $users->changeColumn('term', 'integer', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $users = $this->table('banhist');
        $users->changeColumn('term', 'integer')
            ->save();
    }
}
