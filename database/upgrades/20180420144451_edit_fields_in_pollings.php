<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInPollings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM pollings');

        foreach($rows as $row) {
            if (empty($row['created_at'])) {
                $this->execute('UPDATE pollings SET created_at="'.SITETIME.'" WHERE id="'.$row['id'].'" LIMIT 1;');
            }
        }

        $table = $this->table('pollings');
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
