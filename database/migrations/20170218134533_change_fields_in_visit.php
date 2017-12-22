<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInVisit extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('visit');

        $table
            ->removeIndexByName('user')
            ->save();

        $rows = $this->fetchAll('SELECT * FROM visit');
        foreach($rows as $row) {

            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }

            if (! empty($user['id'])) {
                $this->execute('UPDATE visit SET user="' . $user['id'] . '" WHERE id = "' . $row['id'] . '" LIMIT 1;');
            } else {
                $this->execute('DELETE FROM visit WHERE id = "' . $row['id'] . '" LIMIT 1;');
            }
        }

        $table
            ->changeColumn('user', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('nowtime', 'updated_at');

        $table->addIndex('user_id', ['unique' => true])->save();
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
            ->removeIndexByName('user_id')
            ->addIndex('user', ['unique' => true])
            ->save();

    }
}
