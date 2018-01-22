<?php

use Phinx\Migration\AbstractMigration;

class ChangeBirthdayInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');

        $table
            ->changeColumn('joined', 'string')
            ->save();

        $rows = $this->fetchAll('SELECT * FROM users;');
        foreach($rows as $row) {

            $birthday = ! empty($row['birthday']) ? date('Y-m-d', strtotime($row['birthday'])) : null;
            $joined   = date('Y-m-d', (int) $row['joined']);

            if (validateDate($birthday, 'Y-m-d')) {
                $birthday = '"'.$birthday.'"';
            } else {
                $birthday = 'null';
            }

            $this->execute('UPDATE users SET birthday='.$birthday.', joined="'.$joined.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->changeColumn('joined', 'date')
            ->changeColumn('birthday', 'date', ['null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table
            ->changeColumn('joined', 'string')
            ->changeColumn('birthday', 'string', ['limit' => 10, 'null' => true])
            ->save();

        $rows = $this->fetchAll('SELECT * FROM users');
        foreach($rows as $row) {

            $birthday = ! empty($row['birthday']) ? date('d.m.Y', strtotime($row['birthday'])) : null;

            if (validateDate($birthday, 'd.m.Y')) {
                $birthday = '"'.$birthday.'"';
            } else {
                $birthday = 'null';
            }

            list($year, $month, $day) = explode('-', $row['joined']);

            $joined = mktime(0, 0, 0, $month, $day + 1, $year);

            $this->execute('UPDATE users SET birthday='.$birthday.', joined="'.$joined.'" WHERE id = "' . $row['id'] . '" LIMIT 1;');
        }

        $table
            ->changeColumn('joined', 'integer')
            ->save();
    }
}
