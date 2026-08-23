<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetentionPolicy extends Model
{
    protected $fillable = ['record_type', 'retention_months', 'action'];
}
