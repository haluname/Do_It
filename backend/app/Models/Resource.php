<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'type', 'title', 'slug', 'author', 'year', 
        'description', 'content', 'file', 'file_name', 'mime_type'
    ];
}