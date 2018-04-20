<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInTopics extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM topics');

        foreach($rows as $row) {
            if (empty($row['created_at'])) {
                $this->execute('UPDATE topics SET created_at="'.SITETIME.'" WHERE id="'.$row['id'].'" LIMIT 1;');
            }
        }

        $table = $this->table('topics');
        $table
            ->changeColumn('forum_id', 'integer')
            ->changeColumn('count_posts', 'integer')
            ->changeColumn('last_post_id', 'integer', ['null' => true])
            ->changeColumn('created_at', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
