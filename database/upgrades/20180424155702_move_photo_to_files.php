<?php

use App\Models\Photo;
use Phinx\Migration\AbstractMigration;

class MovePhotoToFiles extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM photo');

        foreach($rows as $row) {

            $file = UPLOADS . '/pictures/' . $row['link'];

            if (file_exists($file) && is_file($file)) {
                $filesize = filesize($file);
            } else {
                $filesize = 0;
            }

            $this->execute('INSERT INTO files (relate_id, relate_type, hash, name, size, user_id, created_at) VALUES (' . $row['id'] . ', "' . addslashes(Photo::class) . '", "' . $row['link'] . '", "' . $row['link'] . '", ' . $filesize . ', ' . $row['user_id'] . ', ' . $row['created_at'] . ');');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
