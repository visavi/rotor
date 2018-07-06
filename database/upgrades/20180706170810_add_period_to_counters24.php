<?php

use Phinx\Migration\AbstractMigration;

class AddPeriodToCounters24 extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counters24');
        $table->addColumn('period', 'datetime', ['after' => 'id', 'null' => true])
            ->save();

        $rows = $this->fetchAll('SELECT * FROM counters24');

        foreach($rows as $row) {

            $period = date('Y-m-d H:00:00', $row['hour'] * 3600);

            $this->execute('UPDATE counters24 SET period="'.$period.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('counters24');
        $table->removeColumn('period')
            ->save();
    }
}
