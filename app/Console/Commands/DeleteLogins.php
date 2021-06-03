<?php

namespace App\Console\Commands;

use App\Models\Login;
use Illuminate\Console\Command;

class DeleteLogins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:logins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete history login';

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
     * Удаляет старые записи истории авторизаций
     *
     * @return int
     */
    public function handle()
    {
        Login::query()
            ->where('created_at', '<', strtotime('-3 month', SITETIME))
            ->delete();

        $this->info('History login successfully deleted.');

        return 0;
    }
}
