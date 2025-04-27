<?php

namespace App\Enum;

enum YarnWeight: string
{
    case SUPER_FINE = 'Super Fine';
    case FINE = 'Fine';
    case LIGHT = 'Light';
    case MEDIUM = 'Medium';
    case BULKY = 'Bulky';
    case SUPER_BULKY = 'Super Bulky';
}
