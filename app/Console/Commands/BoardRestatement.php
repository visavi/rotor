<?php

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class BoardRestatement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'board:restatement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Board restatement';

    /**
     * Пересчитывает счетчик объявлений
     */
    public function handle(): int
    {
        Item::query()
            ->active()
            ->where('expires_at', '<', SITETIME)
            ->each(function ($item) {
                $item->category->decrement('count_items');
                $item->update([
                    'active' => false,
                ]);
            });

        $this->info('Board restatement successfully.');

        return SymfonyCommand::SUCCESS;
    }
}
