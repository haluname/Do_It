<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetOtp extends Model
{
    use HasFactory;

    // app/Models/PasswordResetOtp.php
protected $fillable = ['email', 'code', 'expires_at'];

protected $dates = ['expires_at'];
}
