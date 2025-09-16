<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'phone',
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
        'password' => 'hashed',
    ];

    /**
     * Relationship với app user profiles
     */
    public function appProfiles(): HasMany
    {
        return $this->hasMany(AppUserProfile::class);
    }

    public function profile($appId)
    {
        return $this->appProfiles()->where('app_id', $appId)->first();
    }

    /**
     * Relationship với voucher wallet
     */
    public function voucherWallet(): HasMany
    {
        return $this->hasMany(VoucherUserWallet::class);
    }

    /**
     * Relationship với points ledger
     */
    public function pointsLedger(): HasMany
    {
        return $this->hasMany(PointsLedger::class);
    }

    /**
     * Relationship với transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Lấy profile của user trong app cụ thể
     */
    public function getProfileInApp($appId)
    {
        return $this->appProfiles()->where('app_id', $appId)->first();
    }
}
