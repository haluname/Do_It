<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Thread extends Model
{
 use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'content',
        'pinned',
        'closed',
        'views_count',
        'last_post_id'
    ];

        protected $with = ['category', 'user'];


    protected $casts = [
        'pinned' => 'boolean',
        'closed' => 'boolean',
        'created_at' => 'datetime:d M Y',
    ];

    // Relazioni
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function lastPost()
    {
        return $this->belongsTo(Post::class, 'last_post_id');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($thread) {
            $thread->slug = Str::slug($thread->title) . '-' . Str::random(6);
        });
    }

    public function getExcerptAttribute()
    {
        return Str::limit(strip_tags($this->content), 200);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
