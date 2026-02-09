<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    protected $fillable = [
        'sender', 
        'message', 
        'received_at', 
        'is_read', 
        'processed_by'
    ];
}
//testing
// use Illuminate\Support\Facades\Schedule;
// Schedule::call([GsmController::class, 'fetchMessages'])->everyMinute();

// pip install pyserial - ini

// Inside php artisan tinker
// $controller = new \App\Http\Controllers\GsmController();
// $response = $controller->fetchMessages();

// // To see the actual data (the messages)
// $response->getContent();


// php artisan import:barangays --file=path/to/barangays.csv

// ollama pull granite3.2:8b 

# This sets it permanently
// [System.Environment]::SetEnvironmentVariable('OLLAMA_ORIGINS', '*', 'User')

# This sets it for the current window
// $env:OLLAMA_ORIGINS = "*"