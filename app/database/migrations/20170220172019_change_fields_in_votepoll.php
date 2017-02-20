<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInVotepoll extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {

        $rows = $this->fetchAll('SELECT * FROM votepoll');
        foreach($rows as $row) {

            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }

            if (! empty($user['id'])) {
                $this->execute('UPDATE votepoll SET user="' . $user['id'] . '" WHERE id = "' . $row['id'] . '" LIMIT 1;');
            } else {
                $this->execute('DELETE FROM votepoll WHERE id = "' . $row['id'] . '" LIMIT 1;');
            }
        }

        $table = $this->table('votepoll');
        $table
            ->changeColumn('user', 'integer')
            ->changeColumn('time', 'integer', ['null' => true])
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('time', 'created_at');


    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('votepoll');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('created_at', 'time')
            ->save();
    }
}
