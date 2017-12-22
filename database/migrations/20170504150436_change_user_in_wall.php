<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInWall extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('wall');

        $rows = $this->fetchAll('SELECT * FROM wall');
        foreach($rows as $row) {

            $user = 0;
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $author = 0;
            if (!empty($row['login'])) {
                $author = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['login'].'" LIMIT 1;');
            }
            $authorId = ! empty($author) ? $author['id'] : 0;

            $this->execute('UPDATE wall SET user="'.$userId.'", login="'.$authorId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer')
            ->changeColumn('login', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('login', 'author_id');
        $table->renameColumn('time', 'created_at');

        $table
            ->removeIndexByName('user')
            ->addIndex('user_id')
            ->addIndex('created_at')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('wall');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('author_id', 'login')
            ->renameColumn('created_at', 'time')
            ->save();

        $table
            ->removeIndexByName('created_at')
            ->removeIndexByName('user_id')
            ->addIndex('user')
            ->save();
    }
}
