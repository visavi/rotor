<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInFlood extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('flood');

        $rows = $this->fetchAll('SELECT * FROM flood');
        foreach($rows as $row) {

            $user = 0;
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE flood SET user="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
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
        $table = $this->table('flood');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('created_at', 'time')
            ->save();

        $table
            ->removeIndexByName('user_id')
            ->addIndex('user')
            ->save();
    }
}
