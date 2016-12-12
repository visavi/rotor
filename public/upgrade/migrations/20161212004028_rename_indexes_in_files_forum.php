<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInFilesForum extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('files_forum');
        $table->removeIndexByName('file_topics_id');
        $table->removeIndexByName('file_posts_id');
        $table->removeIndexByName('file_user');
        $table->removeIndexByName('file_time');
        $table->addIndex('topic_id')
            ->addIndex('post_id')
            ->addIndex('user')
            ->addIndex('time')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('files_forum');
        $table->removeIndexByName('topic_id');
        $table->removeIndexByName('post_id');
        $table->removeIndexByName('user');
        $table->removeIndexByName('time');
        $table->addIndex('topic_id', ['name' => 'file_topics_id'])
            ->addIndex('post_id', ['name' => 'file_posts_id'])
            ->addIndex('user', ['name' => 'file_user'])
            ->addIndex('time', ['name' => 'file_time'])
            ->save();
    }
}
