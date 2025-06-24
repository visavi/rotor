<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class DeletePending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete pending user';

    /**
     * Удаляет не активированные аккаунты
     */
    public function handle(): int
    {
        if (setting('regkeys')) {
            $users = User::query()
                ->where('level', User::PENDED)
                ->where('created_at', '<', strtotime('-1 day', SITETIME))
                ->get();

            foreach ($users as $user) {
                $user->delete();
            }
        } else {
            User::query()
                ->where('level', User::PENDED)
                ->update([
                    'level' => User::USER,
                ]);
        }

        $this->info('Pending user successfully deleted.');

        return SymfonyCommand::SUCCESS;
    }
}
