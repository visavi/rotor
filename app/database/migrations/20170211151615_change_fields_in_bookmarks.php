<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInBookmarks extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('bookmarks');

        $rows = $this->fetchAll('SELECT * FROM bookmarks');
        foreach($rows as $row) {

            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }

            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE bookmarks SET user="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer')
            ->save();

         $table->renameColumn('user', 'user_id');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('bookmarks');
        $table
            ->renameColumn('user_id', 'user')
            ->save();
    }
}
