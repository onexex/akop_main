<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\GsmController;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    // This manually instantiates the controller to avoid the static error
    $controller = new GsmController();
    $controller->fetchMessages();
})
->everyMinute()
->description('Checking GSM Module for SMS')
->onSuccess(function () {
    echo "\n [" . now()->format('H:i:s') . "] GSM Task: Messages checked and updated successfully.\n";
})
->onFailure(function () {
    echo "\n [" . now()->format('H:i:s') . "] GSM Task: Failed to communicate with SIM800C.\n";
});
