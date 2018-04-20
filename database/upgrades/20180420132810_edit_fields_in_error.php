<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInError extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM error');

        foreach($rows as $row) {
            if (empty($row['created_at'])) {
                $this->execute('UPDATE error SET created_at="'.SITETIME.'" WHERE id="'.$row['id'].'" LIMIT 1;');
            }
        }

        $table = $this->table('error');
        $table
            ->changeColumn('code', 'integer')
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
