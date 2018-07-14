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
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        //$schedule->command('UpdateVATSIM')->everyMinute();

        $schedule->command('spider:at')->weeklyOn(1, '03:30');
        $schedule->command('spider:ch')->dailyAt('02:40');
        $schedule->command('spider:de')->dailyAt('00:00');
        $schedule->command("spider:fr")->dailyAt("02:00");
        $schedule->command('spider:hk')->monthlyOn(21, '01:30');
        $schedule->command('spider:hu')->dailyAt('02:30');
        $schedule->command('spider:ir')->dailyAt('03:00');
        $schedule->command('spider:lv')->weeklyOn(3, '03:40');
        $schedule->command('spider:mx')->weeklyOn(1, '3:45');
        $schedule->command('spider:pl')->weeklyOn(1,'03:40');
        $schedule->command("spider:pt")->monthlyOn(5, '01:30');
        $schedule->command('spider:pl')->weeklyOn(2,'03:40');
        $schedule->command("spider:si")->dailyAt("04:00");
        $schedule->command('spider:us')->dailyAt('01:00');
        $schedule->command('spider:uk')->monthlyOn(1, '01:30');

        $schedule->command('airport:cache')->dailyAt('09:00');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
