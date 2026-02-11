<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopItem extends Model
{
    protected $fillable = ['checklist_id', 'task', 'description', 'is_photo_required', 'sort_order'];
}
