<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherUserWallet extends Model
{
    use HasFactory;

    protected $table = 'voucher_user_wallet';

    protected $fillable = [
        'user_id',
        'app_id',
        'voucher_id',
        'code',
        'status',
        'redeemed_at',
        'used_at',
        'expire_at',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
        'used_at' => 'datetime',
        'expire_at' => 'datetime',
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
     * Relationship với Voucher
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Scope theo app
     */
    public function scopeForApp($query, $appId)
    {
        return $query->where('app_id', $appId);
    }

    /**
     * Scope theo status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope vouchers chưa hết hạn
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expire_at')
              ->orWhere('expire_at', '>', now());
        });
    }

    /**
     * Kiểm tra voucher có thể sử dụng không
     */
    public function canUse(): bool
    {
        if ($this->status !== 'redeemed') {
            return false;
        }

        if ($this->expire_at && $this->expire_at < now()) {
            return false;
        }

        return true;
    }
}
