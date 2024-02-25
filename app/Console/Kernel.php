<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('board:restatement')->hourly();
        $schedule->command('delete:files')->daily();
        $schedule->command('delete:logins')->daily();
        $schedule->command('delete:logs')->daily();
        $schedule->command('delete:pending')->daily();
        $schedule->command('delete:polling')->weekly();
        $schedule->command('delete:readers')->weekly();
        $schedule->command('add:subscribers')->hourly();
        $schedule->command('add:birthdays')->dailyAt('07:00');
        $schedule->command('message:send')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
