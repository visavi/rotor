<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInBanhist extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('banhist');

        $rows = $this->fetchAll('SELECT * FROM banhist');

        foreach($rows as $row) {
            $user = 0;
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $sendUser = 0;
            if (!empty($row['send'])) {
                $sendUser = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['send'].'" LIMIT 1;');
            }
            $sendUserId = ! empty($sendUser) ? $sendUser['id'] : 0;

            $this->execute('UPDATE banhist SET user="'.$userId.'", send="'.$sendUserId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer')
            ->changeColumn('send', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('send', 'send_user_id');
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
        $table = $this->table('banhist');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('send_user_id', 'send')
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
