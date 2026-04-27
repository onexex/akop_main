<?php

namespace App\Enums\Permissions;

enum SurveyPermission: string
{

    case access_survey = 'access survey';
    case view_survey = 'view survey';
    case create_survey = 'create survey';
    case edit_survey = 'edit survey';
    case delete_survey = 'delete survey';
}
