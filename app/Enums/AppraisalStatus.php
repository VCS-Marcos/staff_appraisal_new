<?php

namespace App\Enums;

enum AppraisalStatus: string
{
    case Draft = 'draft';
    case PendingEmployee = 'pending_employee';
    case PendingReviewer = 'pending_reviewer';
    case PendingSignoff = 'pending_signoff';
    case Completed = 'completed';

    /** Every status that means "started but not finished" (used by the "In Progress" list filter). */
    public const IN_PROGRESS = ['pending_employee', 'pending_reviewer', 'pending_signoff'];

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingEmployee => 'Awaiting Employee',
            self::PendingReviewer => 'Awaiting Reviewer',
            self::PendingSignoff => 'Awaiting Sign-off',
            self::Completed => 'Completed',
        };
    }
}
