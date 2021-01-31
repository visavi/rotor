<?php

declare(strict_types=1);

use App\Migrations\Migration;

final class ChangeFulltextIndexInDowns extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (config('DB_DRIVER') === 'mysql') {
            $this->db->getConnection()->statement('ALTER TABLE downs DROP INDEX title;');
            $this->db->getConnection()->statement('ALTER TABLE downs DROP INDEX text;');

            $this->db->getConnection()->statement('CREATE FULLTEXT INDEX downs_title_text_fulltext ON downs(title, text);');
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        if (config('DB_DRIVER') === 'mysql') {
            $this->db->getConnection()->statement('ALTER TABLE downs DROP INDEX downs_title_text_fulltext;');

            $this->db->getConnection()->statement('CREATE FULLTEXT INDEX title ON downs(title);');
            $this->db->getConnection()->statement('CREATE FULLTEXT INDEX text ON downs(text);');
        }
    }
}
