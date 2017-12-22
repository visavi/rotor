<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInTransfers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('transfers');

        $rows = $this->fetchAll('SELECT * FROM transfers');
        foreach($rows as $row) {

            $user = 0;
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $recipient = 0;
            if (!empty($row['login'])) {
                $recipient = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['login'].'" LIMIT 1;');
            }
            $recipientId = ! empty($recipient) ? $recipient['id'] : 0;

            $this->execute('UPDATE transfers SET user="'.$userId.'", login="'.$recipientId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer')
            ->changeColumn('login', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('login', 'recipient_id');
        $table->renameColumn('time', 'created_at');

        $table
            ->removeIndexByName('user')
            ->removeIndexByName('login')
            ->removeIndexByName('time')
            ->addIndex('user_id')
            ->addIndex('recipient_id')
            ->addIndex('created_at')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('transfers');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('recipient_id', 'login')
            ->renameColumn('created_at', 'time')
            ->save();

        $table
            ->removeIndexByName('user_id')
            ->removeIndexByName('recipient_id')
            ->removeIndexByName('created_at')
            ->addIndex('user')
            ->addIndex('login')
            ->addIndex('time')
            ->save();
    }
}
