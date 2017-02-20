<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInNote extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('note');
        $table->removeIndexByName('user')->save();
        
        $rows = $this->fetchAll('SELECT * FROM note');
        foreach($rows as $row) {

            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }

            if (!empty($row['user'])) {
                $editUser = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['edit'].'" LIMIT 1;');
            }
            $editUserId = ! empty($editUser) ? $editUser['id'] : 0;

            if (! empty($user['id'])) {
                $this->execute('UPDATE note SET user="' . $user['id'] . '", edit="' . $editUserId . '" WHERE id = "' . $row['id'] . '" LIMIT 1;');
            } else {
                $this->execute('DELETE FROM note WHERE id = "' . $row['id'] . '" LIMIT 1;');
            }
        }

        $table
            ->changeColumn('user', 'integer')
            ->changeColumn('edit', 'integer')
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
