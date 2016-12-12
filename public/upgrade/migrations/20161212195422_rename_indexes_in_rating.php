<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInRating extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('rating');
        $table->removeIndexByName('rating_user');
        $table->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('rating');
        $table->removeIndexByName('user');
        $table->addIndex('user', ['name' => 'rating_time'])
            ->save();
    }
}
