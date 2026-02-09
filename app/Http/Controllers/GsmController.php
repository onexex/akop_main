<?php

namespace App\Http\Controllers;

use App\Models\SmsMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class GsmController extends Controller
{
    /**
     * Fetch and sync messages from the GSM Module
     */
    public function fetchMessages()
    {
        // 1. Run the Python Script
        // Use base_path to ensure it finds the file regardless of where the command is called from
        // $scriptPath = base_path('gsm_receive.py');
        $result = Process::run("python gsm_receive.py");

        if (!$result->successful()) {
            Log::error("GSM Hardware Error: " . $result->errorOutput());
            return response()->json(['error' => 'Could not communicate with Modem'], 500);
        }

        // 2. Decode the JSON from Python
        $inbox = json_decode($result->output(), true);

        // Check if Python returned an error inside the JSON
        if (isset($inbox[0]['error'])) {
            Log::error("Python Script Error: " . $inbox[0]['error']);
            return response()->json(['error' => $inbox[0]['error']], 500);
        }

        $syncedCount = 0;

        if (is_array($inbox)) {
            foreach ($inbox as $sms) {
                // 3. Logic: Update or Create
                // If sender + received_at exists, it updates the message (helpful for late concatenation)
                // Otherwise, it creates a new record.
                SmsMessage::updateOrCreate(
                    [
                        'sender'      => $sms['sender'],
                        'received_at' => $sms['time'],
                    ],
                    [
                        'message'     => $sms['message']
                    ]
                );
                $syncedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'messages_processed' => $syncedCount,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}