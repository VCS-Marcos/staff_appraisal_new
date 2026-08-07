<?php

namespace App\Enums;

enum OverallRating: string
{
    case DidNotMeetAllTargets = 'Did Not Meet All Targets';
    case MetAllTargets = 'Met All Targets';
    case ExceededAllTargets = 'Exceeded All Targets';
}
