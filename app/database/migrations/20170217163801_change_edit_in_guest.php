<?php

use Phinx\Migration\AbstractMigration;

class ChangeEditInGuest extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM guest');
        foreach($rows as $row) {

            if (!empty($row['user'])) {
                $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$row['edit'].'" LIMIT 1;');
            }

            $userId = ! empty($user) ? $user['id'] : 0;

            $this->execute('UPDATE guest SET edit="'.$userId.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table = $this->table('guest');
        $table
            ->changeColumn('edit', 'integer')
            ->removeIndex('time')
            ->save();

        $table->renameColumn('edit', 'edit_user_id');
        $table->renameColumn('time', 'created_at');
        $table->renameColumn('edit_time', 'updated_at');

        $table->changeColumn('edit_user_id', 'integer', ['null' => true])
            ->changeColumn('created_at', 'integer', ['null' => true])
            ->changeColumn('updated_at', 'integer', ['null' => true])
            ->addIndex('created_at')
            ->save();

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('guest');
        $table
            ->removeIndex('created_at')
            ->renameColumn('edit_user_id', 'edit')
            ->renameColumn('created_at', 'time')
            ->renameColumn('updated_at', 'edit_time')
            ->save();

        $table
            ->addIndex('time')
            ->save();
    }
}
