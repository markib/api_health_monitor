<?php

use Illuminate\Console\Scheduling\Schedule;

return function (Schedule $schedule) {
    $schedule->command('app:monitor-endpoints')
         ->everyFiveSeconds()
    ->withoutOverlapping();
};