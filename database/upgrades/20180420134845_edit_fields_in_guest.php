<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInGuest extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM guest');

        foreach($rows as $row) {
            if (empty($row['created_at'])) {
                $this->execute('UPDATE guest SET created_at="'.SITETIME.'" WHERE id="'.$row['id'].'" LIMIT 1;');
            }
        }

        $table = $this->table('guest');
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
