<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'title',
        'description',
        'exp'
    ];

    protected $casts = [
        'exp' => 'date'
    ];

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

}
