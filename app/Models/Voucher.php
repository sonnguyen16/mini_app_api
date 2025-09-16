<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'category_id',
        'name',
        'description',
        'image',
        'detail',
        'required_points',
        'expire_at',
        'usage_condition',
        'quantity',
        'active',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
        'required_points' => 'integer',
        'quantity' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * Relationship với App
     */
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    /**
     * Relationship với Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relationship với voucher wallet
     */
    public function walletEntries(): HasMany
    {
        return $this->hasMany(VoucherUserWallet::class);
    }

    /**
     * Relationship với transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope cho active vouchers
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
     * Scope vouchers còn số lượng
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('quantity')
              ->orWhereRaw('quantity > (SELECT COUNT(*) FROM voucher_user_wallet WHERE voucher_id = vouchers.id)');
        });
    }

    /**
     * Kiểm tra voucher có thể đổi không
     */
    public function canRedeem(): bool
    {
        if (!$this->active) {
            return false;
        }

        if ($this->expire_at && $this->expire_at < now()) {
            return false;
        }

        if ($this->quantity) {
            $redeemedCount = $this->walletEntries()->count();
            if ($redeemedCount >= $this->quantity) {
                return false;
            }
        }

        return true;
    }
}
