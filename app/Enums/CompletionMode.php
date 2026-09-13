<?php

namespace App\Enums;

enum CompletionMode: string
{
    case SelfService = 'self_service';
    case Assisted = 'assisted';

    public function label(): string
    {
        return match ($this) {
            self::SelfService => 'Self-Service',
            self::Assisted => 'Assisted (In Person)',
        };
    }
}
