<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'kitchen_id',
        'user_id',
        'reviewer_id',
        'month',
        'year',
        'sop_compliance',
        'hygiene',
        'punctuality',
        'teamwork',
        'technical_skill',
        'total_score',
        'bonus_amount',
        'comments',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function calculateBonus($maxVariable)
    {
        if ($maxVariable <= 0)
            return 0;
        return round(($this->total_score / 25) * $maxVariable, 2);
    }
}
