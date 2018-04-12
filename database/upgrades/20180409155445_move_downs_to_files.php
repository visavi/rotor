<?php

use App\Models\Down;
use Phinx\Migration\AbstractMigration;

class MoveDownsToFiles extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT * FROM downs');

        foreach($rows as $row) {

            $file = UPLOADS.'/files/'.$row['link'];

            if (file_exists($file) && is_file($file)) {

                $filesize = filesize($file);
                $newName  = uniqueName(getExtension($file));

                rename($file, UPLOADS.'/files/'.$newName);
            } else {
                $filesize = 0;
                $newName  = '';
            }

            $this->execute('INSERT INTO files (relate_id, relate_type, hash, name, size, user_id, created_at) VALUES (' . $row['id'] . ', "' . addslashes(Down::class) . '", "' . $newName . '", "' . $row['link'] . '", ' . $filesize . ', ' . $row['user_id'] . ', ' . $row['created_at'] . ');');

            if ($row['screen']) {
                $file = UPLOADS.'/screen/'.$row['screen'];

                if (file_exists($file) && is_file($file)) {
                    $filesize = filesize($file);
                    $newName  = uniqueName(getExtension($file));

                    rename($file, UPLOADS.'/screen/'.$newName);
                } else {
                    $filesize = 0;
                    $newName  = '';
                }

                $this->execute('INSERT INTO files (relate_id, relate_type, hash, name, size, user_id, created_at) VALUES (' . $row['id'] . ', "' . addslashes(Down::class) . '", "' . $newName . '", "' . $row['screen'] . '", ' . $filesize . ', ' . $row['user_id'] . ', ' . $row['created_at'] . ');');
            }
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
