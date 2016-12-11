<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInBlogs extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('blogs');
        $table->removeIndexByName('blogs_cats_id');
        $table->removeIndexByName('blogs_time');
        $table->removeIndexByName('blogs_user');
        $table->addIndex('category_id')
            ->addIndex('time')
            ->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('blogs');
        $table->removeIndexByName('category_id');
        $table->removeIndexByName('time');
        $table->removeIndexByName('user');
        $table->addIndex('category_id', ['name' => 'blogs_cats_id'])
            ->addIndex('time', ['name' => 'blogs_time'])
            ->addIndex('user', ['name' => 'blogs_user'])
            ->save();
    }
}
