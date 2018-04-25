<?php

use Phinx\Migration\AbstractMigration;

class RenameDirectories extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        @rename(STORAGE . '/backup', STORAGE . '/backups');
        @rename(STORAGE . '/cache', STORAGE . '/caches');

        @rename(UPLOADS . '/forum', UPLOADS . '/forums');
        @rename(UPLOADS . '/screen', UPLOADS . '/screens');
        @rename(UPLOADS . '/thumbnail', UPLOADS . '/thumbnails');
        @rename(UPLOADS . '/photos', UPLOADS . '/photos_temp');
        @rename(UPLOADS . '/pictures', UPLOADS . '/photos');
        @rename(UPLOADS . '/photos_temp', UPLOADS . '/pictures');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        @rename(STORAGE . '/backups', STORAGE . '/backup');
        @rename(STORAGE . '/caches', STORAGE . '/cache');

        @rename(UPLOADS . '/forums', UPLOADS . '/forum');
        @rename(UPLOADS . '/screens', UPLOADS . '/screen');
        @rename(UPLOADS . '/thumbnails', UPLOADS . '/thumbnail');
        @rename(UPLOADS . '/pictures', UPLOADS . '/pictures_temp');
        @rename(UPLOADS . '/photos', UPLOADS . '/pictures');
        @rename(UPLOADS . '/pictures_temp', UPLOADS . '/photos');
    }
}
