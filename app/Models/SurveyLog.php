<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyLog extends Model
{
    protected $table = 'survey_logs';
    protected $fillable = [
        'caption',
        'remarks',
        'address_tag',
        'latitude',
        'longitude',
        'sync_status',
    ];

    public function assignedUsers(): HasMany
    {
        return $this->hasMany(SurveyAssignedUser::class, 'survey_log_id');
    }
     
   public function addressProperty()
    { 
       
        return $this->belongsTo(Barangay::class, 'address_tag', 'id'); 
    }
}