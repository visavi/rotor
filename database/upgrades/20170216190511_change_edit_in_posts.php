<?php

use Phinx\Migration\AbstractMigration;

class ChangeEditInPosts extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {

        $rows = $this->fetchAll('SELECT * FROM posts');
        foreach($rows as $row) {

            $user = 0;
            if (!empty($row['edit'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['edit'].'" LIMIT 1;');
            }

            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE posts SET edit="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table = $this->table('posts');
        $table
            ->changeColumn('edit', 'integer')
            ->save();

        $table->renameColumn('edit', 'edit_user_id');
        $table->renameColumn('time', 'created_at');
        $table->renameColumn('edit_time', 'updated_at');

        $table->changeColumn('edit_user_id', 'integer', ['null' => true])
            ->changeColumn('created_at', 'integer', ['null' => true])
            ->changeColumn('updated_at', 'integer', ['null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('posts');
        $table
            ->renameColumn('edit_user_id', 'edit')
            ->renameColumn('created_at', 'time')
            ->renameColumn('updated_at', 'edit_time')
            ->save();
    }
}
