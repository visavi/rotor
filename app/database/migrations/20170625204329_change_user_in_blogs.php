<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInBlogs extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('blogs');

        $rows = $this->fetchAll('SELECT * FROM blogs');
        foreach($rows as $row) {

            $user = 0;
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE blogs SET user="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('time', 'created_at');

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
        $table = $this->table('blogs');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('created_at', 'time')
            ->save();

        $table
            ->removeIndexByName('user_id')
            ->removeIndexByName('created_at')
            ->addIndex('user')
            ->addIndex('time')
            ->save();
    }
}
