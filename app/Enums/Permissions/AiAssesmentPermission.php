<?php

namespace App\Enums\Permissions;

enum AiAssesmentPermission: string
{
    case access_ai_assessment = 'access ai assessment';
    case view_ai_assessment = 'view ai assessment';
    case create_ai_assessment = 'create ai assessment';
    case edit_ai_assessment = 'edit ai assessment';
    case delete_ai_assessment = 'delete ai assessment';
}
