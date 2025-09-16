<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class App extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'logo',
        'owner_email',
        'owner_password_hash',
        'owner_name',
        'mini_app_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $hidden = [
        'owner_password_hash',
    ];

    /**
     * Relationship với user owner
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_email', 'email');
    }
    /**
     * Relationship với app user profiles
     */
    public function userProfiles(): HasMany
    {
        return $this->hasMany(AppUserProfile::class);
    }

    /**
     * Relationship với categories
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Relationship với vouchers
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
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
     * Relationship với policies
     */
    public function policies(): HasMany
    {
        return $this->hasMany(AppPolicy::class);
    }

    /**
     * Scope cho active apps
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
