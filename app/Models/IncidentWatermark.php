<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentWatermark extends Model
{
    protected $fillable = [
        'name',
        'type',
        'color',
        'description',
    ];
}
