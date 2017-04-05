<?php

use Phinx\Migration\AbstractMigration;

class ChangeUserInContact extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('contact');

        $rows = $this->fetchAll('SELECT * FROM contact');
        foreach($rows as $row) {

            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['user'].'" LIMIT 1;');
            }
            $userId = ! empty($user) ? $user['id'] : 0;

            if (!empty($row['name'])) {
                $contact = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['name'].'" LIMIT 1;');
            }
            $contactId = ! empty($contact) ? $contact['id'] : 0;

            $this->execute('UPDATE contact SET user="'.$userId.'", name="'.$contactId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('user', 'integer')
            ->changeColumn('name', 'integer')
            ->save();

        $table->renameColumn('user', 'user_id');
        $table->renameColumn('name', 'contact_id');
        $table->renameColumn('time', 'created_at');

        $table
            ->removeIndexByName('user')
            ->removeIndexByName('time')
            ->addIndex('user_id')
            ->addIndex('created_at')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('contact');
        $table
            ->renameColumn('user_id', 'user')
            ->renameColumn('contact_id', 'name')
            ->renameColumn('created_at', 'time')
            ->save();

        $table
            ->removeIndexByName('user_id')
            ->removeIndexByName('created_at')
            ->addIndex('user')
            ->addIndex('time')
            ->save();
    }
}
