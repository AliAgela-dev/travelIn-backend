<?php

namespace App\Enums;

enum UserType: string
{
    case User = 'user';
    case ResortOwner = 'resort_owner';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';
}
