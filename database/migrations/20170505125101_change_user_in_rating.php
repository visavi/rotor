<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInRating extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('rating');

        $rows = $this->fetchAll('SELECT * FROM rating');
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

            $this->execute('UPDATE rating SET user="'.$userId.'", login="'.$recipientId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
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
            ->addIndex('user_id')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('rating');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('recipient_id', 'login')
            ->renameColumn('created_at', 'time')
            ->save();

        $table
            ->removeIndexByName('user_id')
            ->addIndex('user')
            ->save();
    }
}
