<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInChat extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('chat');

        $rows = $this->fetchAll('SELECT * FROM chat');
        foreach($rows as $row) {

            $user = 0;
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $editUser = 0;
            if (!empty($row['edit'])) {
                $editUser = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['edit'].'" LIMIT 1;');
            }
            $editUserId = ! empty($editUser) ? $editUser['id'] : 0;

            $this->execute('UPDATE chat SET user="'.$userId.'", edit="'.$editUserId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer')
            ->changeColumn('edit', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('edit', 'edit_user_id');
        $table->renameColumn('time', 'created_at');
        $table->renameColumn('edit_time', 'updated_at');

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
        $table = $this->table('chat');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('edit_user_id', 'edit')
            ->renameColumn('created_at', 'time')
            ->renameColumn('updated_at', 'edit_time')
            ->save();

        $table
            ->removeIndexByName('created_at')
            ->addIndex('time')
            ->save();
    }
}
