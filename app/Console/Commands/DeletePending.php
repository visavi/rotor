<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Удаляет не активированные аккаунты
     *
     * @return int
     */
    public function handle()
    {
        if (setting('regkeys')) {
            $users = User::query()
                ->where('level', User::PENDED)
                ->where('created_at', '<', strtotime('-1 day', SITETIME))
                ->get();

            foreach ($users as $user) {
                $user->delete();
            }
        }

        $this->info('Pending user successfully deleted.');

        return 0;
    }
}
