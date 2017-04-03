<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInTrash extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('trash');

        $rows = $this->fetchAll('SELECT * FROM trash');
        foreach($rows as $row) {

            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            if (!empty($row['author'])) {
                $author = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['author'].'" LIMIT 1;');
            }
            $authorId = ! empty($author) ? $author['id'] : 0;

            $this->execute('UPDATE trash SET user="'.$userId.'", author="'.$authorId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer')
            ->changeColumn('author', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('author', 'author_id');
        $table->renameColumn('time', 'created_at');
        $table->renameColumn('del', 'deleted_at');

        $table
            ->removeIndexByName('user')
            ->removeIndexByName('time')
            ->addIndex('user_id')
            ->addIndex('created_at')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('trash');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('author_id', 'author')
            ->renameColumn('created_at', 'time')
            ->renameColumn('deleted_at', 'del')
            ->save();

        $table
            ->removeIndexByName('user_id')
            ->removeIndexByName('created_at')
            ->addIndex('user')
            ->addIndex('time')
            ->save();
    }
}
