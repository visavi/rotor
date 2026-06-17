<?php

namespace App\Console\Commands;

use App\Classes\Registry;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class SearchImport extends Command
{
    protected $signature = 'search:import {--model= : Import only this model class (short name or FQCN)}';
    protected $description = 'Sync existing records to search index';

    /**
     * Handle
     */
    public function handle(): int
    {
        $allModels = array_merge([
            User::class,
            Comment::class,
        ], array_column(Registry::$search, 'class'));

        $filter = $this->option('model');

        if ($filter) {
            $models = array_values(array_filter($allModels, fn ($m) => $m === $filter || class_basename($m) === $filter));

            if (empty($models)) {
                $this->error("Model '{$filter}' not found in search registry.");

                return SymfonyCommand::FAILURE;
            }

            foreach ($models as $modelClass) {
                DB::table('search')
                    ->where('relate_type', (new $modelClass())->getMorphClass())
                    ->delete();
            }
        } else {
            $models = $allModels;
            DB::table('search')->truncate();
        }

        DB::disableQueryLog();
        DB::connection()->unsetEventDispatcher();

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

            $model->chunkById(1000, function ($records) use ($progressBar, &$total) {
                $searchData = [];

                foreach ($records as $record) {
                    if (! $record->shouldBeSearchable()) {
                        $progressBar->advance();
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
                    DB::table('search')->insertOrIgnore($searchData);
                }
            });

            $progressBar->finish();
            $this->info(PHP_EOL . "Successfully indexed {$total} of {$count} records for {$modelClass}");
        } catch (\Exception $e) {
            $this->error("Error processing {$modelClass}: " . $e->getMessage());
        }
    }
}
