<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('monitor:endpoints')
    ->everyTenMinutes()
    ->withoutOverlapping();
