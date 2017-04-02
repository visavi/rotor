<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInBan extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('ban');

        $rows = $this->fetchAll('SELECT * FROM ban');
        foreach($rows as $row) {

            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE ban SET user="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer', ['null' => true])
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('time', 'created_at');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('ban');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('created_at', 'time')
            ->save();
    }
}
