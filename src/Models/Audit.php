<?php

namespace Jeevanjoshi\LaravelAuditTrail\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Audit extends Model
{
    protected $table = 'audit_trails';

    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'changed_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * The model that was changed
     * Works with ANY model - User, Invoice, Order etc
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo(
            __FUNCTION__,
            'model_type',
            'model_id'
        );
    }

    /**
     * The user who made the change
     */
    public function changer(): BelongsTo
    {
        $userModel = config('audit-trail.user_model', 'App\Models\User');
        return $this->belongsTo($userModel, 'changed_by');
    }

    /**
     * Scope - filter by action
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope - filter by model type
     */
    public function scopeForModel($query, string $model)
    {
        return $query->where('model_type', $model);
    }

    /**
     * Scope - filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('changed_by', $userId);
    }

    /**
     * Scope - today's audits
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope - this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }
}
