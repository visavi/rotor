<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInPosts extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM posts');
        foreach($rows as $row) {
            $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');

            if ($user) {
                $this->execute('UPDATE posts SET user="'.$user['id'].'" WHERE id = "'.$row['id'].'" LIMIT 1;');
            }

        }

        $this->execute('UPDATE posts SET user=0 WHERE user NOT REGEXP \'^[0-9]+$\';');

        $table = $this->table('posts');
        $table->changeColumn('user', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('posts');
        $table->changeColumn('user_id', 'string', ['limit' => 20])
            ->save();

        $table->renameColumn('user_id', 'user');
    }
}
