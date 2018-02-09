<?php

use App\Models\Vote;
use Phinx\Migration\AbstractMigration;

class MoveVotepollToPollings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM votepoll;');

        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'relate_type' => Vote::class,
                'relate_id'   => $row['vote_id'],
                'user_id'     => $row['user_id'],
                'vote'        => '+',
                'created_at'  => $row['created_at'],
            ];
        }

        $table = $this->table('pollings');
        $table->insert($data)->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DELETE FROM pollings WHERE relate_type = "' . addslashes(Vote::class) . '";');
    }
}
