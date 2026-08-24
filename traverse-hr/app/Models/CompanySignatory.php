<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Who signs on Traverse Inc.'s behalf. The Super Admin uploads a signature
 * image per signatory (the founder, a senior manager, an admin) and picks
 * which one countersigns a given document — so a junior can complete an
 * onboarding session without the founder present.
 */
class CompanySignatory extends Model
{
    protected $fillable = ['name', 'designation', 'signature_image_path', 'is_default', 'active', 'created_by'];

    protected $casts = ['is_default' => 'boolean', 'active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public static function default(): ?self
    {
        return static::active()->where('is_default', true)->first()
            ?? static::active()->orderBy('id')->first();
    }
}
