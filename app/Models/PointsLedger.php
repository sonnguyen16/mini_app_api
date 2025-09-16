<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsLedger extends Model
{
    use HasFactory;

    protected $table = 'points_ledger';

    protected $fillable = [
        'user_id',
        'app_id',
        'phone_snapshot',
        'amount',
        'reason',
        'ref_type',
        'ref_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'integer',
        'ref_id' => 'integer',
        'created_by' => 'integer',
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
     * Relationship với creator (admin/app_owner)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope theo app
     */
    public function scopeForApp($query, $appId)
    {
        return $query->where('app_id', $appId);
    }

    /**
     * Scope theo user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope theo loại reference
     */
    public function scopeByRefType($query, $refType)
    {
        return $query->where('ref_type', $refType);
    }
}
