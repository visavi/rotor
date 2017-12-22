<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInPollings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM pollings');
        foreach($rows as $row) {

            $user = 0;
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }

            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE pollings SET user="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table = $this->table('pollings');
        $table
            ->addColumn('updated_at', 'integer', ['null' => true])
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
        $table = $this->table('pollings');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('created_at', 'time')
            ->removeColumn('updated_at')
            ->save();
    }
}
