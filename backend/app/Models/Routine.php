<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Routine extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'title', 
        'description', 
        'frequency', 
        'last_executed', 
        'next_due',
        'completions',
        'start_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}