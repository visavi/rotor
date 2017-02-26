<?php

use Phinx\Migration\AbstractMigration;

class ChangeModeratorsInTopics extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('topics');
        $table
            ->changeColumn('time', 'integer', ['null' => true])
            ->addColumn('created_at', 'integer', ['null' => true])
            ->save();

        $table->renameColumn('time', 'updated_at');

        $table
            ->removeIndexByName('time')
            ->addIndex('updated_at')
            ->save();

        $rows = $this->fetchAll('SELECT * FROM topics');
        foreach($rows as $row) {

            $mods = [];

            if (!empty($row['moderators'])) {
                $moderators = explode(',', $row['moderators']);

                foreach ($moderators as $moderator) {
                    $user = $this->fetchRow('SELECT id FROM users WHERE login = "'.$moderator.'" LIMIT 1;');

                    if (! empty($user['id'])) {
                        $mods[] = $user['id'];
                    }
                }
            }

            $firstPost = $this->fetchRow('SELECT created_at FROM `posts` WHERE `topic_id`="'.$row['id'].'" ORDER BY id ASC LIMIT 1;');


                $newMods  = (! empty($mods)) ? implode(',', $mods) : null;
                $timePost = (! empty($firstPost['created_at'])) ? $firstPost['created_at'] : 0;

                $this->execute("UPDATE topics SET moderators='" . $newMods . "', created_at='".$timePost."' WHERE id = '" . $row['id'] . "' LIMIT 1;");

        }

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('topics');
        $table
            ->removeColumn('created_at')
            ->renameColumn('updated_at', 'time')
            ->save();

        $table
            ->removeIndexByName('updated_at')
            ->addIndex('time')
            ->save();
    }
}
