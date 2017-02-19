<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInNote extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('DELETE FROM note WHERE user REGEXP \'^[0-9]+$\';');

        $rows = $this->fetchAll('SELECT * FROM note');
        foreach($rows as $row) {

            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }

            if (!empty($row['user'])) {
                $editUser = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['edit'].'" LIMIT 1;');
            }

            $userId = ! empty($user) ? $user['id'] : 0;
            $editUserId = ! empty($editUser) ? $editUser['id'] : 0;

            $this->execute('UPDATE note SET user="'.$userId.'", edit="'.$editUserId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table = $this->table('note');
        $table
            ->changeColumn('user', 'integer')
            ->changeColumn('edit', 'integer')
            ->removeIndexByName('user')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('edit', 'edit_user_id');
        $table->renameColumn('time', 'updated_at');

        $table
            ->addIndex('user_id', ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('note');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('edit_user_id', 'edit')
            ->renameColumn('updated_at', 'time')
            ->removeIndexByName('user_id')
            ->addIndex('user', ['unique' => true])
            ->save();
    }
}
