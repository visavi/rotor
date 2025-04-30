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

    public function handle(): int
    {
        $models = [
            Article::class,
            Comment::class,
            Down::class,
            Guestbook::class,
            // Item::class,
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

            $query = method_exists($model, 'active')
                ? $model->active()
                : $model->newQuery();

            $count = $query->count();
            $this->line("Found {$count} records to index");

            $progressBar = $this->output->createProgressBar($count);
            $progressBar->start();

            // Используем chunkById для стабильной пакетной обработки
            $query->chunkById(1000, function ($records) use ($progressBar) {
                $searchData = [];

                foreach ($records as $record) {
                    if (! $record->shouldBeSearchable()) {
                        continue;
                    }

                    $searchData[] = [
                        'text'        => $this->buildSearchTextForRecord($record),
                        'created_at'  => $record->created_at,
                        'relate_type' => $record::$morphName,
                        'relate_id'   => $record->getKey(),
                    ];

                    $progressBar->advance();
                }

                DB::table('search')->upsert(
                    $searchData,
                    ['relate_type', 'relate_id'],
                    ['text', 'created_at'],
                );
            });

            $progressBar->finish();
            $this->info(PHP_EOL . "Successfully indexed {$count} records for {$modelClass}");
        } catch (\Exception $e) {
            $this->error("Error processing {$modelClass}: " . $e->getMessage());
        }
    }

    protected function buildSearchTextForRecord($record): string
    {
        $fields = $record->searchableFields();
        $values = [];

        foreach ($fields as $field) {
            if (isset($record->$field)) {
                $values[] = $record->$field;
            }
        }

        return bbCode(implode(' ', $values), false);
    }
}
