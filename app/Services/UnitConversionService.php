<?php

namespace App\Services;

use App\Enums\Unit;

class UnitConversionService
{
    /**
     * Convert a value from one unit to another.
     */
    public function convert(float $value, Unit|string $fromUnit, Unit|string $toUnit): float
    {
        if (is_string($fromUnit)) {
            $fromUnit = Unit::tryFrom($fromUnit) ?? Unit::Gram;
        }
        if (is_string($toUnit)) {
            $toUnit = Unit::tryFrom($toUnit) ?? Unit::Gram;
        }
        if ($fromUnit === $toUnit) {
            return $value;
        }
        if (!$fromUnit->canConvertTo($toUnit)) {
            return $value;
        }
        return $fromUnit->convertTo($value, $toUnit);
    }

    /**
     * Get a standardized unit for calculation (e.g. grams for weight).
     */
    public function getBaseUnit(Unit $unit): Unit
    {
        $weightUnits = [Unit::Gram, Unit::Kilogram];
        $volumeUnits = [Unit::Milliliter, Unit::Liter, Unit::Tablespoon, Unit::Teaspoon, Unit::Cup];

        if (in_array($unit, $weightUnits)) {
            return Unit::Gram;
        }

        if (in_array($unit, $volumeUnits)) {
            return Unit::Milliliter;
        }

        return $unit; // Piece stays piece
    }
}
