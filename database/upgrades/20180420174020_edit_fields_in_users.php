<?php

use Phinx\Migration\AbstractMigration;

class EditFieldsInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM users');

        foreach($rows as $row) {
            if ($row['money'] >= 2147483648) {
                $this->execute('UPDATE users SET money=2147483640 WHERE id="'.$row['id'].'" LIMIT 1;');
            }
        }

        $table = $this->table('users');
        $table
            ->changeColumn('visits', 'integer', ['default' => 0])
            ->changeColumn('newprivat', 'integer', ['default' => 0])
            ->changeColumn('newwall', 'integer', ['default' => 0])
            ->changeColumn('allforum', 'integer', ['default' => 0])
            ->changeColumn('allguest', 'integer', ['default' => 0])
            ->changeColumn('allcomments', 'integer', ['default' => 0])
            ->changeColumn('point', 'integer', ['default' => 0])
            ->changeColumn('money', 'integer', ['default' => 0])
            ->changeColumn('rating', 'integer', ['default' => 0])
            ->changeColumn('posrating', 'integer', ['default' => 0])
            ->changeColumn('negrating', 'integer', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
