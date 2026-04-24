<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Sync SMS messages every 5 minutes
        $schedule->command('sms:sync')->everyFiveMinutes()->withoutOverlapping();
        
        // Sync SMS messages every hour (backup)
        $schedule->command('sms:sync --limit=1000')->hourly()->withoutOverlapping();
        
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        // Register the SMS sync command
        $this->registerCommand(\App\Console\Commands\SyncSmsCommand::class);

        require base_path('routes/console.php');
    }
}
