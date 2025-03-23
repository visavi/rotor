<?php

namespace App\Console\Commands;

use App\Models\Login;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

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
     * Удаляет старые записи истории авторизаций
     */
    public function handle(): int
    {
        Login::query()
            ->where('created_at', '<', strtotime('-3 month', SITETIME))
            ->delete();

        $this->info('History login successfully deleted.');

        return SymfonyCommand::SUCCESS;
    }
}
