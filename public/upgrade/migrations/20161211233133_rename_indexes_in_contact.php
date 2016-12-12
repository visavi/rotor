<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInContact extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('contact');
        $table->removeIndexByName('contact_time');
        $table->removeIndexByName('contact_user');
        $table->addIndex('time')
            ->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('contact');
        $table->removeIndexByName('time');
        $table->removeIndexByName('user');
        $table->addIndex('time', ['name' => 'contact_time'])
            ->addIndex('user', ['name' => 'contact_user'])
            ->save();
    }
}
