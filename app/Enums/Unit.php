<?php

namespace App\Enums;

enum Unit: string
{
    case Gram = 'g';
    case Kilogram = 'kg';
    case Milliliter = 'ml';
    case Liter = 'l';
    case Piece = 'pcs';
    case Tablespoon = 'tbsp';
    case Teaspoon = 'tsp';
    case Cup = 'cup';

    public function label(): string
    {
        return match ($this) {
            self::Gram => 'Gram (g)',
            self::Kilogram => 'Kilogram (kg)',
            self::Milliliter => 'Milliliter (ml)',
            self::Liter => 'Liter (l)',
            self::Piece => 'Piece (pcs)',
            self::Tablespoon => 'Tablespoon (tbsp)',
            self::Teaspoon => 'Teaspoon (tsp)',
            self::Cup => 'Cup',
        };
    }

    /**
     * Conversion disabled - returns input value (simplified system)
     */
    public function convertTo(float $value, Unit $toUnit): float
    {
        return $value;
    }

    /**
     * Check if conversion is possible between units
     */
    public function canConvertTo(Unit $toUnit): bool
    {
        $weightUnits = [self::Gram, self::Kilogram];
        $volumeUnits = [self::Milliliter, self::Liter, self::Tablespoon, self::Teaspoon, self::Cup];

        if ($this === self::Piece || $toUnit === self::Piece) {
            return $this === $toUnit;
        }

        if (in_array($this, $weightUnits) && in_array($toUnit, $weightUnits)) {
            return true;
        }

        if (in_array($this, $volumeUnits) && in_array($toUnit, $volumeUnits)) {
            return true;
        }

        return false;
    }
}
