<?php

namespace Jeevanjoshi\LaravelAuditTrail;

use Illuminate\Database\Eloquent\Model;
use Jeevanjoshi\LaravelAuditTrail\Models\Audit;

class AuditObserver
{
    /**
     * Fires when a model is created
     */
    public function created(Model $model): void
    {
        $this->log($model, 'created', [], $model->getAttributes());
    }

    /**
     * Fires when a model is updated
     */
    public function updated(Model $model): void
    {
        $dirty = $model->getDirty();
        $excluded = $this->getExcludedFields($model);

        $changed = array_diff_key($dirty, array_flip($excluded));

        if (empty($changed)) {
            return;
        }

        $old = array_intersect_key($model->getOriginal(), $changed);
        $new = array_intersect_key($model->getAttributes(), $changed);

        $this->log($model, 'updated', $old, $new);
    }

    /**
     * Fires when a model is deleted
     */
    public function deleted(Model $model): void
    {
        $this->log($model, 'deleted', $model->getAttributes(), []);
    }

    /**
     * Core logging method
     */
    private function log(
        Model $model,
        string $action,
        array $oldValues,
        array $newValues
    ): void {
        $excluded = $this->getExcludedFields($model);

        $oldValues = array_diff_key($oldValues, array_flip($excluded));
        $newValues = array_diff_key($newValues, array_flip($excluded));

        Audit::create([
            'model_type' => get_class($model),
            'model_id'   => $model->getKey(),
            'action'     => $action,
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'changed_by' => auth()->id(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Get excluded fields from model or config
     */

    private function getExcludedFields(Model $model): array
    {
        $globalExcludes = config('audit-trail.exclude_fields', [
            'password',
            'remember_token',
            'updated_at',
        ]);

        // Ensure it's always an array even if config returns null
        if (!is_array($globalExcludes)) {
            $globalExcludes = [
                'password',
                'remember_token',
                'updated_at',
            ];
        }

        $modelExcludes = [];

        if (property_exists($model, 'auditExclude') && is_array($model->auditExclude)) {
            $modelExcludes = $model->auditExclude;
        }

        return array_merge($globalExcludes, $modelExcludes);

    }
}
