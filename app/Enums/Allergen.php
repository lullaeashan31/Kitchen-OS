<?php

namespace App\Enums;

enum Allergen: string
{
    case Peanuts = 'peanuts';
    case TreeNuts = 'tree_nuts';
    case Milk = 'milk';
    case Eggs = 'eggs';
    case Wheat = 'wheat';
    case Soy = 'soy';
    case Fish = 'fish';
    case Shellfish = 'shellfish';
    case Sesame = 'sesame';
    case Mustard = 'mustard';
    case Celery = 'celery';
    case Sulfites = 'sulfites';
    case Lupin = 'lupin';
    case Molluscs = 'molluscs';

    public function label(): string
    {
        return match ($this) {
            self::Peanuts => 'Peanuts',
            self::TreeNuts => 'Tree Nuts',
            self::Milk => 'Milk',
            self::Eggs => 'Eggs',
            self::Wheat => 'Wheat / Gluten',
            self::Soy => 'Soy',
            self::Fish => 'Fish',
            self::Shellfish => 'Shellfish (Crustaceans)',
            self::Sesame => 'Sesame',
            self::Mustard => 'Mustard',
            self::Celery => 'Celery',
            self::Sulfites => 'Sulfites',
            self::Lupin => 'Lupin',
            self::Molluscs => 'Molluscs',
        };
    }
}
