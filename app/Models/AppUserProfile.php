<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppUserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'app_id',
        'name',
        'birthday',
        'gender',
        'address',
        'points_total',
        'active',
    ];

    protected $casts = [
        'birthday' => 'date',
        'active' => 'boolean',
        'points_total' => 'integer',
    ];

    /**
     * Relationship với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship với App
     */
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    /**
     * Relationship với voucher wallet
     */
    public function voucherWallet(): HasMany
    {
        return $this->hasMany(VoucherUserWallet::class, 'user_id', 'user_id')
                    ->where('app_id', $this->app_id);
    }

    /**
     * Relationship với points ledger
     */
    public function pointsLedger(): HasMany
    {
        return $this->hasMany(PointsLedger::class, 'user_id', 'user_id')
                    ->where('app_id', $this->app_id);
    }

    /**
     * Scope cho active profiles
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope theo app
     */
    public function scopeForApp($query, $appId)
    {
        return $query->where('app_id', $appId);
    }

    /**
     * Scope tìm theo phone
     */
    public function scopeByPhone($query, $phone, $appId)
    {
        return $query->whereHas('user', function ($q) use ($phone) {
            $q->where('phone', $phone);
        })->where('app_id', $appId);
    }
}
