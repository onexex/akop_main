<?php

namespace App\Enums\Permissions;

enum RolePermission: string
{
    case access_role_management = 'access role management';
    case view_role = 'view role';
    case create_role = 'create role';
    case edit_role = 'edit role';
    case delete_role = 'delete role';
}
