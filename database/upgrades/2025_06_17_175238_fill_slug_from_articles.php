<?php

use App\Models\Article;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Article::query()->chunk(200, function ($articles) {
            foreach ($articles as $article) {
                $article->update([
                    'slug' => Str::slug($article->title),
                ]);
            }
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('slug')->nullable()->change();
        });
    }
};
