<?php

namespace App\Enums\Permissions;

enum RequestPermission: string
{
    case view_request = 'view request';
    case create_request = 'create request';
    case edit_request = 'edit request';
    case delete_request = 'delete request';
    case approve_request_level_1 = 'approve request level 1';
    case approve_request_level_2 = 'approve request level 2';
    case release_request = 'release request';
}
