<?php

use Phinx\Migration\AbstractMigration;

class ChangeEditFieldsInTopics extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('topics');
        $table->addColumn('last_post_id', 'integer', ['default' => 0])
            ->removeColumn('last_user')
            ->renameColumn('last_time', 'time')
            ->save();

        $rows = $this->fetchAll('SELECT * FROM topics');
        foreach($rows as $row) {
            $post = $this->fetchRow('SELECT id, time FROM posts WHERE topic_id = "'.$row['id'].'" ORDER BY time DESC LIMIT 1;');

            if ($post) {
                $this->execute('UPDATE topics SET `time` = "'.$post['time'].'", last_post_id="'.$post['id'].'" WHERE id = "'.$row['id'].'" LIMIT 1;');
            }
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('topics');
        $table->removeColumn('last_post_id')
            ->addColumn('last_user', 'string', ['limit' => 20, 'null' => true])
            ->renameColumn('time', 'last_time')
            ->save();
    }
}
