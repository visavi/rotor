<?php

use Phinx\Migration\AbstractMigration;

class MoveInboxToMessages extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Входящие
        $rows = $this->fetchAll('SELECT * FROM inbox;');

        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'user_id'    => $row['user_id'],
                'author_id'  => $row['author_id'] ?? 0,
                'text'       => $row['text'],
                'type'       => 'in',
                'read'       => 1,
                'created_at' => $row['created_at'],
            ];
        }

        $table = $this->table('messages');
        foreach (array_chunk($data, 1000) as $insert) {
            $table->insert($insert)->save();
            unset($insert);
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('TRUNCATE messages');
    }
}
