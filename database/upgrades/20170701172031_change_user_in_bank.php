<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInBank extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('bank');

        $rows = $this->fetchAll('SELECT * FROM bank');
        foreach($rows as $row) {

            $user = 0;
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "' . $row['user'] . '" LIMIT 1;');
            }
            $userId = !empty($user) ? $user['id'] : 0;

            if ($userId) {
                $this->execute('UPDATE bank SET user="' . $userId . '" WHERE id = "' . $row['id'] . '" LIMIT 1;');
            } else {
                $this->execute('DELETE FROM bank WHERE id = "' . $row['id'] . '" LIMIT 1;');
            }
        }

        $table
            ->changeColumn('user', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('time', 'created_at');

        $table
            ->removeIndexByName('user')
            ->addIndex('user_id', ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('bank');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('created_at', 'time')
            ->save();

        $table
            ->removeIndexByName('user_id')
            ->addIndex('user', ['unique' => true])
            ->save();
    }
}
