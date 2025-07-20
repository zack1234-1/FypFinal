<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('subscriptions:check')
        ->everyFifteenMinutes();// Adjust this as per your needs (e.g., hourly, twice daily, etc.)
        $schedule->command('auth:clear-resets')->everyFifteenMinutes();
        $schedule->command('reminders:send')->everyMinute();
        $schedule->command('recurring-tasks:generate')->daily()->at('00:00')->withoutOverlapping(); // Add this line
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
