<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Run intake reminders every 5 minutes
        $schedule->command('send:intakes-reminders --minutes=15')->everyFiveMinutes();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
