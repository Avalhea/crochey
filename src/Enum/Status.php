<?php


namespace App\Enum;

enum Status: string
{
    case NOT_STARTED = 'Not started';
    case WIP = 'WIP';
    case FINISHED = 'Finished';

}
