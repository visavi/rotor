<?php

use Phinx\Migration\AbstractMigration;

class MoveOutboxToMessages extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Исходящие
        $rows = $this->fetchAll('SELECT * FROM outbox;');

        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'user_id'    => $row['user_id'],
                'author_id'  => $row['recipient_id'],
                'text'       => $row['text'],
                'type'       => 'out',
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
        $this->execute('DELETE FROM messages WHERE type="out";');
    }
}
