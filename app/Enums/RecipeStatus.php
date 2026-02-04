<?php

namespace App\Enums;

enum RecipeStatus: string
{
    case Draft = 'draft';
    case Permanent = 'permanent';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Permanent => 'Permanent',
            self::Rejected => 'Rejected',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'warning',
            self::Permanent => 'success',
            self::Rejected => 'danger',
        };
    }
}
