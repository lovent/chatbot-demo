<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ChatworkDaily::class,
        Commands\ChatworkComTrua::class,
        Commands\ChatworkUnipos::class,
        Commands\ChatworkAwesomeDaily::class,
        Commands\ChatworkTinhTinh::class,
        Commands\NAComTrua::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command("chatwork:send-daily-reminder")->weekdays()->dailyAt("16:45");
        // $schedule->command("chatwork:send-daily-lunch")->weekdays()->dailyAt("11:15");
        $schedule->command("chatwork:send-daily-lunch-to-na")->weekdays()->dailyAt("11:35");
        // $schedule->command("chatwork:send-weekly-unipos")->fridays()->at("10:00");
        $schedule->command("chatwork:send-awesome-report")->weekdays()->dailyAt("08:00");
        // $schedule->command("chatwork:tinh-tinh")->when(function () {
        //     $today = Carbon::now();

        //     $pickedDay = Carbon::now()->endOfMonth();

        //     if ($pickedDay->isSunday()) {
        //         $pickedDay->subDay();
        //     }

        //     if ($pickedDay->isSaturday()) {
        //         $pickedDay->subDay();
        //     }

        //     return $today->equalTo($pickedDay);
        // })->at("10:00");
    }
}
