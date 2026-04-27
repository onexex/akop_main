<?php

namespace App\Enums\Permissions;

enum GeospatialPermission:string
{
    case access_geospatial = 'access geospatial';
    case view_geospatial = 'view geospatial';
    case create_geospatial = 'create geospatial';
    case edit_geospatial = 'edit geospatial';
    case delete_geospatial = 'delete geospatial';
}
