<?php

namespace App\Console\Commands;

use App\Models\File;
use Illuminate\Console\Command;

class DeleteFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete unattached files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Удаляет не прикрепленные файлы
     *
     * @return int
     */
    public function handle()
    {
        $files = File::query()
            ->where('relate_id', 0)
            ->where('created_at', '<', strtotime('-1 day', SITETIME))
            ->get();

        foreach ($files as $file) {
            $file->delete();
        }

        $this->info('Files successfully deleted.');

        return 0;
    }
}
