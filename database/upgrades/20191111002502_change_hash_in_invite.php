<?php

use Phinx\Migration\AbstractMigration;

class ChangeHashInInvite extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('invite');
        $table
            ->changeColumn('hash', 'string', ['limit' => 16])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('invite');
        $table
            ->changeColumn('hash', 'string', ['limit' => 15])
            ->save();
    }
}
