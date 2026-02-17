<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    protected $fillable = [
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
