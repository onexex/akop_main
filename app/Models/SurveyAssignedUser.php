<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyAssignedUser extends Model
{
    protected $table = 'survey_assigned_users';

    protected $fillable = [
        'survey_log_id',
        'assigned_user',
        'latitude',
        'longitude',
    ];

    /**
     * Relationship: Ang user ay kabilang sa isang partikular na SurveyLog.
     */
    public function surveyLog(): BelongsTo
    {
        return $this->belongsTo(SurveyLog::class, 'survey_log_id');
    }
}