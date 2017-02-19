<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInVisit extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {

        $this->execute('DELETE FROM visit WHERE user REGEXP \'^[0-9]+$\';');

        $rows = $this->fetchAll('SELECT * FROM visit');
        foreach($rows as $row) {

            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }

            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE visit SET user="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table = $this->table('visit');
        $table
            ->changeColumn('user', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('nowtime', 'updated_at');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('visit');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('updated_at', 'nowtime')
            ->save();

    }
}
