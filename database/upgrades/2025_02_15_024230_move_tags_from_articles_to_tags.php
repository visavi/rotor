<?php

use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $uniqueTags = [];

        $articles = Article::query()->select('id', 'tags')->get();

        foreach ($articles as $article) {
            $tags = preg_split('/[\s]*[,][\s]*/', $article->tags);

            foreach ($tags as $key => $tag) {
                $tag = Str::lower(trim($tag));

                if (! $tag || Str::length($tag) < 2) {
                    continue;
                }

                if (! in_array($tag, $uniqueTags)) {
                    $uniqueTags[] = $tag;
                }

                $tagId = Tag::query()->where('name', $tag)->value('id');

                if (! $tagId) {
                    $tagId = Tag::query()->insertGetId([
                        'name' => $tag,
                    ]);
                }

                ArticleTag::query()->insert([
                    'article_id' => $article->id,
                    'tag_id'     => $tagId,
                    'sort'       => $key,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Очищаем таблицы
        ArticleTag::query()->truncate();
        Tag::query()->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
