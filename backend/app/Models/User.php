<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use App\Models\Goal;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\File;



class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'level',
        'experience',
        'forum_role',
        'signature',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */


    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    // ... altre parti della classe ...

    /**
     * Relazione con i file dell'utente
     * 
     * @return HasMany<File>
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function addExperience(int $points)
    {
        $this->experience += $points;

        while (true) {
            $required = $this->experienceToNextLevel();

            if ($this->experience < $required) {
                break;
            }

            $this->experience -= $required;
            $this->level++;
        }

        $this->save();
    }

    public function experienceToNextLevel()
    {
        return pow($this->level * 30, 1.5);
    }

    public function currentLevelProgress()
    {
        return ($this->experience / $this->experienceToNextLevel()) * 100;
    }

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // User.php
    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->whereNull('read_at');
    }
}
