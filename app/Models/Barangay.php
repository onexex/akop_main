<?php

// app/Models/Barangay.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    protected $fillable = [
        'region',
        'psgc_region',
        'province',
        'psgc_province',
        'city_municipality',
        'psgc_city',
        'barangay',
        'psgc_barangay',
        'latitude',
        'longitude',
    ];
}
