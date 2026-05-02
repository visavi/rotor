<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->id();
            $table->string('relate_type', 20);
            $table->unsignedInteger('relate_id');
            $table->unsignedInteger('created_at');

            $table->unique(['relate_type', 'relate_id']);
            $table->index('created_at');
            $table->index(['relate_type', 'created_at']);
        });

        // Наполняем последними 500 записями каждого типа
        $inserts = [
            "SELECT 'news', id, created_at FROM news ORDER BY id DESC LIMIT 500",
            "SELECT 'photos', id, created_at FROM photos ORDER BY id DESC LIMIT 500",
            "SELECT 'articles', id, created_at FROM articles WHERE active = 1 ORDER BY id DESC LIMIT 500",
            "SELECT 'downs', id, created_at FROM downs WHERE active = 1 ORDER BY id DESC LIMIT 500",
            "SELECT 'items', id, created_at FROM items WHERE active = 1 AND expires_at > UNIX_TIMESTAMP() ORDER BY id DESC LIMIT 500",
            "SELECT 'offers', id, created_at FROM offers ORDER BY id DESC LIMIT 500",
            "SELECT 'comments', id, created_at FROM comments WHERE id IN (SELECT MAX(id) FROM comments GROUP BY relate_type, relate_id) ORDER BY id DESC LIMIT 500",
            "SELECT 'topics', t.id, p.created_at FROM topics t JOIN posts p ON t.last_post_id = p.id ORDER BY p.id DESC LIMIT 500",
        ];

        foreach ($inserts as $select) {
            DB::statement("INSERT IGNORE INTO feeds (relate_type, relate_id, created_at) $select");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
};
