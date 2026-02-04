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
        // Resolve enums if strings provided
        $from = $fromUnit instanceof Unit ? $fromUnit : Unit::from($fromUnit);
        $to = $toUnit instanceof Unit ? $toUnit : Unit::from($toUnit);

        if (!$from->canConvertTo($to)) {
            throw new \InvalidArgumentException("Cannot convert from {$from->label()} to {$to->label()}");
        }

        return $from->convertTo($value, $to);
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
