<?php

use Phinx\Migration\AbstractMigration;

class AddPeriodToCounters31 extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counters31');
        $table->addColumn('period', 'datetime', ['after' => 'id', 'null' => true])
            ->save();

        $rows = $this->fetchAll('SELECT * FROM counters31');

        foreach($rows as $row) {

            $period = date('Y-m-d 00:00:00', $row['days'] * 86400);

            $this->execute('UPDATE counters31 SET period="'.$period.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('counters31');
        $table->removeColumn('period')
            ->save();
    }
}
