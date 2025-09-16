<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'type',
        'content',
    ];

    /**
     * Relationship với App
     */
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    /**
     * Scope theo app
     */
    public function scopeForApp($query, $appId)
    {
        return $query->where('app_id', $appId);
    }

    /**
     * Scope theo type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
