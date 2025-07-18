<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Down;
use App\Models\Guestbook;
use App\Models\Item;
use App\Models\News;
use App\Models\Offer;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class SearchImport extends Command
{
    protected $signature = 'search:import';
    protected $description = 'Sync existing records to search index';

    /**
     * Handle
     */
    public function handle(): int
    {
        $models = [
            Article::class,
            Comment::class,
            Down::class,
            Guestbook::class,
            Item::class,
            News::class,
            Offer::class,
            Photo::class,
            Post::class,
            Topic::class,
            User::class,
            Vote::class,
        ];

        DB::disableQueryLog();
        DB::connection()->unsetEventDispatcher();
        DB::table('search')->truncate();

        foreach ($models as $modelClass) {
            $this->syncModelRecords($modelClass);
        }

        $this->info('Search index sync completed!');

        return SymfonyCommand::SUCCESS;
    }

    /**
     * SyncModelRecords
     */
    protected function syncModelRecords(string $modelClass): void
    {
        $this->line(PHP_EOL . "Processing model: {$modelClass}");

        try {
            $model = new $modelClass();
            $searchableFields = $model->searchableFields();

            if (empty($searchableFields)) {
                $this->warn("Skipping {$modelClass} - no searchable fields defined");

                return;
            }

            $total = 0;
            $count = $model->count();

            $this->line("Found {$count} records to index");

            $progressBar = $this->output->createProgressBar($count);
            $progressBar->start();

            // Используем chunkById для стабильной пакетной обработки
            $model->chunkById(1000, function ($records) use ($progressBar, &$total) {
                $searchData = [];

                foreach ($records as $record) {
                    if (! $record->shouldBeSearchable()) {
                        continue;
                    }

                    $searchData[] = [
                        'text'        => $record->buildSearchText(),
                        'created_at'  => $record->created_at,
                        'relate_type' => $record->getMorphClass(),
                        'relate_id'   => $record->getKey(),
                    ];

                    $total++;
                    $progressBar->advance();
                }

                if ($searchData) {
                    DB::table('search')->insert($searchData);
                }
            });

            $progressBar->finish();
            $this->info(PHP_EOL . "Successfully indexed {$total} of {$count} records for {$modelClass}");
        } catch (\Exception $e) {
            $this->error("Error processing {$modelClass}: " . $e->getMessage());
        }
    }
}
