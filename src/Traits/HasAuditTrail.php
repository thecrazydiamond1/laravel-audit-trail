<?php

namespace Jeevanjoshi\LaravelAuditTrail\Traits;

use Jeevanjoshi\LaravelAuditTrail\AuditObserver;
use Jeevanjoshi\LaravelAuditTrail\Models\Audit;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAuditTrail
{
    /**
     * Boot the trait - this runs automatically
     * when the trait is used on any model
     */
    public static function bootHasAuditTrail(): void
    {
        static::observe(AuditObserver::class);
    }

    /**
     * Get all audits for this model
     */
    public function audits(): MorphMany
    {
        return $this->morphMany(
            Audit::class,
            'auditable',
            'model_type',
            'model_id'
        );
    }

    /**
     * Revert model to a previous audit state
     */
    public function revertTo(int $auditId): bool
    {
        $audit = $this->audits()->find($auditId);

        if (!$audit || empty($audit->old_values)) {
            return false;
        }

        $this->withoutAudit(function () use ($audit) {
            $this->update($audit->old_values);
        });

        return true;
    }

    /**
     * Perform actions without logging audit
     */
    public function withoutAudit(callable $callback): void
    {
        $dispatcher = static::getEventDispatcher();

        static::unsetEventDispatcher();

        try {
            $callback();
        } finally {
            static::setEventDispatcher($dispatcher);
        }
    }

    /**
     * Get the latest audit for this model
     */
    public function latestAudit(): ?Audit
    {
        return $this->audits()->latest()->first();
    }

    /**
     * Get only updates for this model
     */
    public function updates(): MorphMany
    {
        return $this->audits()->ofAction('updated');
    }

    /**
     * Get only deletes for this model
     */
    public function deletions(): MorphMany
    {
        return $this->audits()->ofAction('deleted');
    }

    /**
     * Check if model has any audit history
     */
    public function hasAuditHistory(): bool
    {
        return $this->audits()->exists();
    }
}
