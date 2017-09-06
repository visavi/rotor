<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInBlacklist extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('blacklist');

        $rows = $this->fetchAll('SELECT * FROM blacklist');
        foreach($rows as $row) {

            $user = 0;
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE blacklist SET user="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('type', 'string', ['limit' => 20])
            ->changeColumn('user', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('time', 'created_at');

        $this->execute('UPDATE blacklist SET type="email" WHERE type = "1";');
        $this->execute('UPDATE blacklist SET type="login" WHERE type = "2";');
        $this->execute('UPDATE blacklist SET type="domain" WHERE type = "3";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('blacklist');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('created_at', 'time')
            ->save();
    }
}
