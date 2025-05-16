<?php

namespace App\Enum;

enum Difficulty: string
{
    case BEGINNER = 'Beginner';
    case EASY = 'Easy';
    case INTERMEDIATE = 'Intermediate';
    case ADVANCED = 'Advanced';
} 