<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInDowns extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('downs');

        $rows = $this->fetchAll('SELECT * FROM downs');
        foreach($rows as $row) {

            $user = 0;
            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE downs SET user="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table->removeColumn('last_load')
            ->removeColumn('author')
            ->removeColumn('site')
            ->changeColumn('user', 'integer')
            ->addColumn('updated_at', 'integer', ['null' => true])
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('time', 'created_at');
        $table->renameColumn('app', 'approved');

        $table->removeIndexByName('time')
            ->addIndex('created_at')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('downs');

        $table->addColumn('last_load', 'integer')
            ->addColumn('author', 'string', ['limit' => 50])
            ->addColumn('site', 'string', ['limit' => 100])
            ->removeColumn('updated_at')
            ->save();

        $table->renameColumn('user_id', 'user');
        $table->renameColumn('created_at', 'time');
        $table->renameColumn('approved', 'app');

        $table->removeIndexByName('created_at')
            ->addIndex('time')
            ->save();
    }
}
