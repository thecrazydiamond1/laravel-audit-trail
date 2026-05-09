<?php

namespace Jeevanjoshi\LaravelAuditTrail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jeevanjoshi\LaravelAuditTrail\Models\Audit;

class AuditTrailController extends Controller
{
    /**
     * Show the audit trail dashboard
     */
    public function index(Request $request)
    {
        $query = Audit::with('changer')
                      ->latest();

        // Filter by model type
        if ($request->filled('model')) {
            $query->forModel($request->model);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->ofAction($request->action);
        }

        // Filter by user
        if ($request->filled('user')) {
            $query->byUser($request->user);
        }

        // Filter by model ID
        if ($request->filled('model_id')) {
            $query->where('model_id', $request->model_id);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $audits = $query->paginate(
            config('audit-trail.per_page', 20)
        );

        $stats = $this->getStats();

        $models = Audit::distinct()
                       ->pluck('model_type')
                       ->map(fn($m) => class_basename($m));

        return view('audit-trail::dashboard', compact(
            'audits',
            'stats',
            'models'
        ));
    }

    /**
     * Get dashboard stats
     */
    public function stats()
    {
        return response()->json($this->getStats());
    }

    /**
     * Revert a model to previous state
     */
    public function revert(Request $request, Audit $audit)
    {
        if (empty($audit->old_values)) {
            return back()->with(
                'error',
                'Cannot revert a created record.'
            );
        }

        $modelClass = $audit->model_type;
        $model = $modelClass::find($audit->model_id);

        if (!$model) {
            return back()->with(
                'error',
                'Original record no longer exists.'
            );
        }

        $model->revertTo($audit->id);

        return back()->with(
            'success',
            'Record reverted successfully.'
        );
    }

    /**
     * Build stats for dashboard
     */
    private function getStats(): array
    {
        return [
            'total'    => Audit::count(),
            'today'    => Audit::today()->count(),
            'deletions' => Audit::ofAction('deleted')->count(),
            'active_users' => Audit::today()
                                   ->distinct()
                                   ->count('changed_by'),
        ];
    }
}
