<?php

namespace App\Enums;

enum LinkedType: string
{
    case Recipe = 'recipe';
    case Production = 'production';
    case Ingredient = 'ingredient';

    public function label(): string
    {
        return match ($this) {
            self::Recipe => 'Recipe',
            self::Production => 'Production',
            self::Ingredient => 'Ingredient',
        };
    }

    public function modelClass(): string
    {
        return match ($this) {
            self::Recipe => \App\Models\Recipe::class,
            self::Production => \App\Models\ProductionDay::class,
            self::Ingredient => \App\Models\Ingredient::class,
        };
    }
}
