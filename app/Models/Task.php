<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'kitchen_id',
        'production_day_id',
        'title',
        'assigned_to',
        'status',
        'proof_image_path',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
        ];
    }

    // Relationships
    public function productionDay()
    {
        return $this->belongsTo(ProductionDay::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Helper Methods
    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::Completed;
    }

    public function isPending(): bool
    {
        return $this->status === TaskStatus::Pending;
    }

    public function markAsCompleted()
    {
        $this->status = TaskStatus::Completed;
        $this->save();
    }
}
