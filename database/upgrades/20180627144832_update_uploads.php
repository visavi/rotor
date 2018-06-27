<?php

use Phinx\Migration\AbstractMigration;

class UpdateUploads extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {

        $directories = glob(UPLOADS . '/forums/*', GLOB_ONLYDIR);

        foreach ($directories as $directory) {

            $files = preg_grep('/^([^.])/', scandir($directory));

            foreach ($files as $file) {
                rename($directory . '/' . $file, UPLOADS . '/forums/' . $file);
            }

            rmdir($directory);
        }

        if (file_exists(UPLOADS . '/screens')) {
            $files = preg_grep('/^([^.])/', scandir(UPLOADS . '/screens'));

            foreach ($files as $file) {
                rename(UPLOADS . '/screens/' . $file, UPLOADS . '/files/' . $file);
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
