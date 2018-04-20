<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInPosts extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM posts');

        foreach($rows as $row) {
            if (empty($row['created_at'])) {
                $this->execute('UPDATE posts SET created_at="'.SITETIME.'" WHERE id="'.$row['id'].'" LIMIT 1;');
            }
        }

        $table = $this->table('posts');
        $table
            ->changeColumn('topic_id', 'integer')
            ->changeColumn('rating', 'integer', ['default' => 0])
            ->changeColumn('created_at', 'integer')
            ->save();

        $table
            ->removeIndex('user_id')
            ->addIndex('user_id')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
