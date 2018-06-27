<?php

use Intervention\Image\ImageManagerStatic as Image;
use Phinx\Migration\AbstractMigration;

class UpdateHashInFiles extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $relates = [
            App\Models\Photo::class => '/uploads/photos',
            App\Models\Blog::class  => '/uploads/blogs',
            App\Models\Down::class  => '/uploads/files',
            App\Models\Item::class  => '/uploads/boards',
            App\Models\Post::class  => '/uploads/forums',
        ];

        $rows = $this->fetchAll('SELECT * FROM files;');

        foreach($rows as $row) {

            $hash = $relates[$row['relate_type']] . '/' . $row['hash'];

            $this->execute('UPDATE files SET hash="'.$hash.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $rows = $this->fetchAll('SELECT * FROM files;');

        foreach($rows as $row) {

            $hash = basename($row['hash']);

            $this->execute('UPDATE files SET hash="'.$hash.'" WHERE id = "'.$row['id'].'" LIMIT 1;');
        }
    }
}
