<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInTopics extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM topics');
        foreach($rows as $row) {
            $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['author'].'" LIMIT 1;');

            if ($user) {
                $this->execute('UPDATE topics SET author="'.$user['id'].'" WHERE id = "'.$row['id'].'" LIMIT 1;');
            }

        }

        $this->execute('UPDATE topics SET author=0 WHERE author NOT REGEXP \'^[0-9]+$\';');

        $table = $this->table('topics');
        $table->changeColumn('author', 'integer')
            ->save();

        $table->renameColumn('author', 'user_id');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('topics');
        $table->changeColumn('user_id', 'string', ['limit' => 20])
            ->save();

        $table->renameColumn('user_id', 'authors');
    }
}
