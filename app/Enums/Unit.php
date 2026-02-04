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
     * Convert value from this unit to target unit
     */
    public function convertTo(float $value, Unit $toUnit): float
    {
        // Convert to base unit first (grams for weight, ml for volume)
        $baseValue = match ($this) {
            self::Gram => $value,
            self::Kilogram => $value * 1000,
            self::Milliliter => $value,
            self::Liter => $value * 1000,
            self::Tablespoon => $value * 15, // 1 tbsp = 15ml
            self::Teaspoon => $value * 5, // 1 tsp = 5ml
            self::Cup => $value * 240, // 1 cup = 240ml
            self::Piece => $value, // Cannot convert pieces
        };

        // Convert from base unit to target unit
        return match ($toUnit) {
            self::Gram => $baseValue,
            self::Kilogram => $baseValue / 1000,
            self::Milliliter => $baseValue,
            self::Liter => $baseValue / 1000,
            self::Tablespoon => $baseValue / 15,
            self::Teaspoon => $baseValue / 5,
            self::Cup => $baseValue / 240,
            self::Piece => $baseValue, // Cannot convert pieces
        };
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
