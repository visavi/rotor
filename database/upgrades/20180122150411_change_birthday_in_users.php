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
            ->changeColumn('birthday', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('updated_at', 'integer', ['null' => true])
            ->addColumn('created_at', 'integer')
            ->save();

        $rows = $this->fetchAll('SELECT * FROM users;');
        foreach($rows as $row) {

            $birthday = ! empty($row['birthday']) ? '"'.date('d.m.Y', strtotime($row['birthday'])).'"' : 'null';
            $joined   = strtotime($row['joined']);
            $updated  = $row['timelastlogin'] ?? 'null';

            if (! is_numeric($joined) || $joined > 2147483647 || (int) abs($joined) !== $joined) {
                $joined = SITETIME;
            }

            $this->execute('UPDATE users SET birthday='.$birthday.', updated_at="'.$updated.'", created_at="'.$joined.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }

        $table
            ->removeColumn('joined')
            ->removeColumn('timelastlogin')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table
            ->addColumn('timelastlogin', 'integer', ['null' => true])
            ->addColumn('joined', 'date', ['null' => true])
            ->save();

        $rows = $this->fetchAll('SELECT * FROM users');
        foreach($rows as $row) {

            $joined        = date('Y-m-d', $row['created_at']);
            $birthday      = ! empty($row['birthday']) ? date('Y-m-d', strtotime($row['birthday'])) : 'null';
            $timelastlogin = $row['updated_at'] ?? 'null';

            if (validateDate($birthday, 'Y-m-d')) {
                $birthday = '"'.$birthday.'"';
            } else {
                $birthday = 'null';
            }

            $this->execute('UPDATE users SET birthday='.$birthday.', joined="'.$joined.'", timelastlogin='.$timelastlogin.' WHERE id = "' . $row['id'] . '" LIMIT 1;');
        }

        $table
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->save();
    }
}
