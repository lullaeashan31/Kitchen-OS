<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class JobRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'outlet_id', 'name', 'department_id', 'default_designation',
        'default_salary_band_min', 'default_salary_band_max',
        'default_document_pack_id', 'default_offer_template_id', 'default_pipeline_id',
        'probation_months', 'notice_period_days', 'sort_order', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Deactivating a role must never break historical records — soft
        // delete only, and block deletion of a role with employees attached.
        static::deleting(function (JobRole $role) {
            if ($role->employees()->exists()) {
                throw new RuntimeException(
                    "Cannot delete job role \"{$role->name}\": employees are still assigned to it. Deactivate it instead."
                );
            }
        });
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
