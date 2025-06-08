<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flashcard extends Model
{
    protected $fillable = ['user_id', 'question', 'answer', 'group_name'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}