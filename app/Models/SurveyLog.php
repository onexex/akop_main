<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyLog extends Model
{
    // Tukuyin ang table name kung hindi ito sumusunod sa plural naming convention
    protected $table = 'survey_logs';

    // Payagan ang mass assignment para sa mga fields na ito
    protected $fillable = [
        'caption',
        'remarks',
        'address_tag',
        'latitude',
        'longitude',
        'sync_status',
    ];

    /**
     * Relationship: Isang SurveyLog ay may maraming Assigned Users.
     */
    public function assignedUsers(): HasMany
    {
        return $this->hasMany(SurveyAssignedUser::class, 'survey_log_id');
    }
}