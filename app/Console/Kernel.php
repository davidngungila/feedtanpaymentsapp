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
        // Real-time SMS sync - every minute for immediate detection
        $schedule->command('sms:sync')->everyMinute()->withoutOverlapping();
        
        // Heavy sync every 5 minutes (more messages)
        $schedule->command('sms:sync --limit=100')->everyFiveMinutes()->withoutOverlapping();
        
        // Full sync every hour (backup)
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
