<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInNotice extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('notice');

        $rows = $this->fetchAll('SELECT * FROM notice');
        foreach($rows as $row) {

            $user = 0;
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE notice SET user="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('time', 'created_at');
        $table->addColumn('updated_at', 'integer', ['after' => 'created_at'])->save();

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('notice');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('created_at', 'time')
            ->removeColumn('updated_at')
            ->save();
    }
}
