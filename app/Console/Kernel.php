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
        Commands\RemainderCron1::class,Commands\RemainderCron2::class,Commands\RemainderCron3::class,Commands\RemainderCron4::class,Commands\RemainderCron5::class,
        //Commands\SendChatNotifications::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('SendChatNotification:hourly')->hourly()->runInBackground();
        //everyMinute
        //$schedule->command('remaindercron:first')->everyMinute()->runInBackground();
        $schedule->command('remaindercron:first')->everyFifteenMinutes()->runInBackground();
        $schedule->command('remaindercron:second')->everyFifteenMinutes()->runInBackground();
        $schedule->command('remaindercron:third')->everyFifteenMinutes()->runInBackground();
        $schedule->command('remaindercron:fourth')->everyFifteenMinutes()->runInBackground();
        $schedule->command('remaindercron:fifth')->everyFifteenMinutes()->runInBackground();
        
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
