<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentAttachment extends Model
{
    protected $fillable = [
        'incident_id',
        'file_name',
        'url',
    ];
}
