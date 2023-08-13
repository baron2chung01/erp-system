<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'web';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    const DISABLED = 0;
    const ACTIVE   = 1;
    const STATUS   = [
        self::DISABLED => "Disabled",
        self::ACTIVE   => "Active",
    ];

    const SUPER_ADMIN  = 0;
    const OFFICE_ADMIN = 1;
    const SITE_ADMIN   = 2;
    const STAFF        = 3;

    const ROLE = [
        self::SUPER_ADMIN  => "Super Admin",
        self::OFFICE_ADMIN => "Office Admin",
        self::SITE_ADMIN   => "Site Admin",
        self::STAFF        => "Staff",
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'login_type',
        'full_name',
        'email',
        'password',
        'email_verified_at',
        'status',
        'role',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
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
    ];

    public function findForPassport($username)
    {
        return $this->where('email', $username)->first();
    }

    public function displayRole(): Attribute
    {
        return Attribute::make(
            get:fn() => self::ROLE[$this->role],
        );
    }
}
