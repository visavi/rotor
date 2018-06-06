<?php

use Intervention\Image\ImageManagerStatic as Image;
use Phinx\Migration\AbstractMigration;

class UpdateAvatars extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {

        $rows = $this->fetchAll('SELECT * FROM users WHERE (avatar IS NULL OR avatar = "") AND (picture IS NOT NULL AND picture <> "");');

        foreach($rows as $row) {

            if (file_exists(UPLOADS . '/pictures/' . $row['picture'])) {
                $avatar = uniqueName('png');

                //-------- Генерируем аватар ----------//
                $img = Image::make(file_get_contents(UPLOADS . '/pictures/' . $row['picture']));
                $img->fit(48);
                $img->save(UPLOADS . '/avatars/' . $avatar);

                $this->execute('UPDATE users SET avatar="'.$avatar.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
            }
        }
    }
}
