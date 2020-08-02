<?php

namespace App\Models;

use App\Traits\UuidModel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, UuidModel, SoftDeletes;

    protected $uuidPrefix = 'usr';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'username', 'email', 'phone_number', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    ///////////////////
    // RELATIONSHIPS //
    ///////////////////

    /**
     * Get the trades for the user.
     */
    public function trades() : HasMany
    {
        return $this->hasMany(Trade::class);
    }

    //////////////
    // MUTATORS //
    //////////////

    /**
     * Encrypt password value before saving.
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function getIsAuthUserAttribute()
    {
        return auth()->user() && auth()->user()->id == $this->id;
    }

    ////////////
    // SCOPES //
    ////////////

    /////////////
    // METHODS //
    /////////////
}
