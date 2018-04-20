<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInSpam extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM spam');

        foreach($rows as $row) {
            if (empty($row['created_at'])) {
                $this->execute('UPDATE spam SET created_at="'.SITETIME.'" WHERE id="'.$row['id'].'" LIMIT 1;');
            }
        }

        $table = $this->table('spam');
        $table
            ->changeColumn('relate_id', 'integer')
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
