<?php

use Phinx\Migration\AbstractMigration;

class ReplaceLoginInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT id, login FROM users WHERE login REGEXP "^[0-9]+$"');

        foreach($rows as $row) {
            $this->execute('UPDATE users SET login="User' . $row['login'] . '" WHERE id="' . $row['id'] . '" LIMIT 1;');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
