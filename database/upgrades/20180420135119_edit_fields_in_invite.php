<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInInvite extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM invite');

        foreach($rows as $row) {
            if (empty($row['created_at'])) {
                $this->execute('UPDATE invite SET created_at="'.SITETIME.'" WHERE id="'.$row['id'].'" LIMIT 1;');
            }
        }

        $table = $this->table('invite');
        $table
            ->changeColumn('created_at', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
