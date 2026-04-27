<?php

namespace App\Enums\Permissions;

enum UserPermissionEnum: string
{
    case access_user_management = 'access user management'; 
    case view_user = 'view user';
    case create_user = 'create user';
    case edit_user = 'edit user';
    case delete_user = 'delete user';

    
}
