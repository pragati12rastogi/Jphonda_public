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
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command('backup:run')->daily();
        $schedule->command('backup:run')->weekly();
        $schedule->command('backup:run')->monthly();
        $schedule->command('backup:run')->yearly();
        $schedule->command('backup:clean')->daily();

        $schedule->call('App\Http\Controllers\admin\TaskController@create_recurrenceTask')->everyMinute();
        $schedule->call('App\Http\Controllers\admin\AutoAssignCall@assignAferFifty')->everyMinute();
        $schedule->call('App\Http\Controllers\admin\HrController@user_inactive')->daily();
        // $schedule->call('App\Http\Controllers\admin\TaskController@create_recurrenceTask')->dailyAt('23:00');
        
        // auto call assign daily
        $schedule->call('app\Http\Controllers\admin\CallSettingController@autoCallAssign')->daily();
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
