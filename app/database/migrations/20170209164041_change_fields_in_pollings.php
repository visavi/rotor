<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInPollings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('pollings');
        $table
            ->addColumn('created_at', 'timestamp', ['null' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->save();

        $rows = $this->fetchAll('SELECT * FROM pollings');
        foreach($rows as $row) {

            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }

            $userId = ! empty($user) ? $user['id'] : 0;
            $date = date('Y-m-d H:i:s', ! empty($row['time']) ? $row['time'] : time());

            $this->execute('UPDATE pollings SET created_at="'.$date.'", user="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer')
            ->removeColumn('time')
            ->save();

         $table->renameColumn('user', 'user_id');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('pollings');
        $table
            ->renameColumn('user_id', 'user')
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->addColumn('time', 'integer')
            ->save();
    }
}
