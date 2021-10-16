<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'is_active'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean'
    ];

    protected $appends = ['gravatar'];

    public function getGravatarAttribute()
    {
        return "https://www.gravatar.com/avatar/"
            . md5($this->email)
            . "?d=mp";
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class);
    }

    public function checks()
    {
        return $this->hasMany(Check::class);
    }
}
