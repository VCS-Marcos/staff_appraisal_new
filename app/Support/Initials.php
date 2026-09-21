<?php

namespace App\Support;

class Initials
{
    public static function of(string $name): string
    {
        return mb_strtoupper(collect(explode(' ', trim($name)))
            ->filter()
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->take(2)
            ->implode(''));
    }
}
