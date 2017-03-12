<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInComments extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('comments');

        $rows = $this->fetchAll('SELECT * FROM comments');
        foreach($rows as $row) {

            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE comments SET user="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('time', 'created_at');

        $table
            ->removeColumn('relate_category_id')
            ->removeIndexByName('time')
            ->addIndex('created_at')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('comments');
        $table
            ->addColumn('relate_category_id', 'integer')
            ->renameColumn('user_id', 'user')
            ->renameColumn('created_at', 'time')
            ->removeIndexByName('created_at')
            ->addIndex('time')
            ->save();

    }
}
