<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInNote extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('note');
        $table->removeIndexByName('note_user');
        $table->addIndex('user', ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('note');
        $table->removeIndexByName('user');
        $table->addIndex('user', ['unique' => true, 'name' => 'note_user'])
            ->save();
    }
}
