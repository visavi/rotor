<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInNews extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('news');

        $rows = $this->fetchAll('SELECT * FROM news');
        foreach($rows as $row) {

            $user = 0;
            if (!empty($row['author'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['author'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE news SET author="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('author', 'integer')
            ->save();

        $table->renameColumn('author', 'user_id');
        $table->renameColumn('time', 'created_at');

        $table
            ->removeIndexByName('time')
            ->addIndex('created_at')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('news');
        $table
            ->renameColumn('user_id', 'author')
            ->renameColumn('created_at', 'time')
            ->removeIndexByName('created_at')
            ->addIndex('time')
            ->save();
    }
}
