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
     * Convert value from this unit to another unit
     */
    public function convertTo(float $value, Unit $toUnit): float
    {
        if ($this === $toUnit) {
            return $value;
        }

        if (!$this->canConvertTo($toUnit)) {
            return $value; // Cannot convert, return original value
        }

        // Conversion factors to base units (g for weight, ml for volume)
        $toBase = match ($this) {
            self::Gram => 1,
            self::Kilogram => 1000,
            self::Milliliter => 1,
            self::Liter => 1000,
            self::Tablespoon => 15,
            self::Teaspoon => 5,
            self::Cup => 240,
            self::Piece => 1,
        };

        $valueInBase = $value * $toBase;

        $fromBase = match ($toUnit) {
            self::Gram => 1,
            self::Kilogram => 1000,
            self::Milliliter => 1,
            self::Liter => 1000,
            self::Tablespoon => 15,
            self::Teaspoon => 5,
            self::Cup => 240,
            self::Piece => 1,
        };

        return $valueInBase / $fromBase;
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
