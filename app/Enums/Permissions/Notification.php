<?php

namespace App\Enums\Permissions;

enum Notification: string
{
    case VIEW = 'view notifications';
    case MANAGE = 'manage notifications';
}
