<?php

namespace App\Enums;

enum RoleName: string
{
    case ADMIN = 'admin';
    case STUDENT = 'student';
    case INSTRUCTOR = 'instructor';
}