<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Reviewer = 'reviewer';
    case Employee = 'employee';
}
