<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInGuest extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM guest');
        foreach($rows as $row) {
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'";');

                if ($user) {
                    $this->execute('UPDATE guest SET user="'.$user['id'].'" WHERE user = "'.$row['user'].'";');
                }
            }
        }

        $this->execute('UPDATE guest SET user=0 WHERE user NOT REGEXP \'^[0-9]+$\';');

        $table = $this->table('guest');
        $table->changeColumn('user', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('guest');
        $table->changeColumn('user_id', 'string', ['limit' => 20])
            ->save();

        $table->renameColumn('user_id', 'user');
    }
}
