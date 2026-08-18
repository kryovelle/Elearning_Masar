<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'users';
     use HasApiTokens, Notifiable;

protected $fillable = [
    'name',
    'email',
    'phone',
    'password',
    'role',
    'photo_url',
    'bio',
    'ccp_number',
    'ccp_name',
    'mfa_enabled',
    'mfa_secret',
    'mfa_recovery_codes',
    'is_blocked',
];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'id',
        'password',
        'remember_token',
        'email_verified_at',
        'created_at',
        'updated_at',
        'mfa_enabled',
        'mfa_secret',
        'mfa_recovery_codes'
    ];
    // app/Models/User.php
protected $casts = [
    'mfa_secret' => 'encrypted',
    'mfa_recovery_codes' => 'array',
    'mfa_enabled' => 'boolean',
];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    function enrollments(){
        return $this->hasMany(Enrollment::class,'student_id')->orderByDesc('enrolled_at');
    }
}
