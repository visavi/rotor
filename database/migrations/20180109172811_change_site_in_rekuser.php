<?php

use Phinx\Migration\AbstractMigration;

class ChangeSiteInRekuser extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('rekuser');
        $table
            ->changeColumn('site', 'string', ['limit' => 100])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('rekuser');
        $table
            ->changeColumn('site', 'string', ['limit' => 50])
            ->save();
    }
}
