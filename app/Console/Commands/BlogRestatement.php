<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class BlogRestatement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:restatement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Blog restatement';

    /**
     * Пересчитывает счетчик объявлений
     */
    public function handle(): int
    {
        Article::query()
            ->active(false)
            ->where('published_at', '<=', now())
            ->each(function (Article $item) {
                $item->category->increment('count_articles');
                $item->update([
                    'active'     => true,
                    'created_at' => strtotime($item->published_at),
                ]);
            });

        $this->info('Blog restatement successfully.');

        return SymfonyCommand::SUCCESS;
    }
}
