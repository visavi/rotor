<?php

use Intervention\Image\ImageManagerStatic as Image;
use Phinx\Migration\AbstractMigration;

class ResizeAvatarsInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT id, avatar, picture FROM users');

        foreach($rows as $row) {
            $avatar = HOME . $row['avatar'];
            $picture = HOME . $row['picture'];

            if (! $row['picture'] || ! file_exists($picture)) {
                continue;
            }

            deleteFile($avatar);

            $img = Image::make($picture);
            $img->fit(64);
            $img->save($avatar);
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
