<?php

declare(strict_types=1);

use App\Migrations\Migration;

final class AddFulltextIndexToArticles extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (config('DB_DRIVER') === 'mysql') {
            $this->db->getConnection()->statement('CREATE FULLTEXT INDEX articles_title_text_fulltext ON articles(title, text);');
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        if (config('DB_DRIVER') === 'mysql') {
            $this->db->getConnection()->statement('ALTER TABLE articles DROP INDEX articles_title_text_fulltext;');
        }
    }
}
