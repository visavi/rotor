<?php

use Phinx\Migration\AbstractMigration;

class ChangeHashInFiles extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('files');
        $table->changeColumn('hash', 'string', ['limit' => 100])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('files');
        $table->changeColumn('hash', 'string', ['limit' => 40])
            ->save();
    }
}
