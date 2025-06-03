<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solution extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'problem', 'solution'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(SolutionLike::class);
    }

    public function likedByUser()
    {
        return $this->hasOne(SolutionLike::class)->where('user_id', auth()->id());
    }
}