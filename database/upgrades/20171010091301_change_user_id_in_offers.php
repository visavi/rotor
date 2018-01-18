<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ChangeUserIdInOffers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('offers');

        $rows = $this->fetchAll('SELECT * FROM offers');

        foreach($rows as $row) {
            $user = 0;
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $replyUser = 0;
            if (!empty($row['user_reply'])) {
                $replyUser = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user_reply'].'" LIMIT 1;');
            }
            $replyUser = ! empty($replyUser) ? $replyUser['id'] : 0;

            $this->execute('UPDATE offers SET user="'.$userId.'", user_reply="'.$replyUser.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('type', 'string', ['limit' => 20])
            ->changeColumn('status', 'string', ['limit' => 20])
            ->changeColumn('user', 'integer')
            ->changeColumn('user_reply', 'integer', ['null' => true])
            ->save();

        $table->renameColumn('text_reply', 'reply');
        $table->renameColumn('user', 'user_id');
        $table->renameColumn('user_reply', 'reply_user_id');
        $table->renameColumn('time', 'created_at');
        $table->renameColumn('time_reply', 'updated_at');

        $table
            ->removeIndexByName('time')
            ->addIndex('created_at')
            ->save();

        $this->execute('UPDATE offers SET type="offer" WHERE type="0";');
        $this->execute('UPDATE offers SET type="issue" WHERE type="1";');


        $this->execute('UPDATE offers SET status="wait" WHERE status="0";');
        $this->execute('UPDATE offers SET status="process" WHERE status="1";');
        $this->execute('UPDATE offers SET status="done" WHERE status="2";');
        $this->execute('UPDATE offers SET status="cancel" WHERE status="3";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('offers');
        $table
            ->renameColumn('reply', 'text_reply')
            ->renameColumn('user_id', 'user')
            ->renameColumn('reply_user_id', 'user_reply')
            ->renameColumn('created_at', 'time')
            ->renameColumn('updated_at', 'time_reply')
            ->save();

        $table
            ->removeIndexByName('created_at')
            ->addIndex('time')
            ->save();
    }
}
