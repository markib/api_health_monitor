<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:monitor-endpoints')
    ->everyTenMinutes()
    ->withoutOverlapping();
