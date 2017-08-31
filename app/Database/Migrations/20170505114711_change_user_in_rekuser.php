<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInRekuser extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('rekuser');

        $rows = $this->fetchAll('SELECT * FROM rekuser');
        foreach($rows as $row) {

            $user = 0;
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE rekuser SET user="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('time', 'created_at');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('rekuser');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('created_at', 'time')
            ->save();
    }
}
