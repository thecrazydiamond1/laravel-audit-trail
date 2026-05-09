<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Audit Trail
    |--------------------------------------------------------------------------
    | Globally enable or disable the audit trail package.
    | When disabled, nothing gets logged and the dashboard is hidden.
    */
    'enabled' => env('AUDIT_TRAIL_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    | The model to use for the "changed_by" relationship.
    | Change this if your user model is in a different namespace.
    */
    'user_model' => env('AUDIT_TRAIL_USER_MODEL', 'App\Models\User'),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    | The URL prefix for the audit trail dashboard.
    | Default: your-app.com/audit-trail
    */
    'route_prefix' => env('AUDIT_TRAIL_ROUTE_PREFIX', 'audit-trail'),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    | Middleware to protect the audit trail dashboard.
    | Add 'auth' to require login, 'admin' for admin only access.
    */
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Excluded Fields
    |--------------------------------------------------------------------------
    | Fields that should NEVER be logged across ALL models.
    | Add sensitive fields here like passwords, tokens, secrets.
    */
    'exclude_fields' => [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'updated_at',
    ],

    /*
    |--------------------------------------------------------------------------
    | Keep Logs For
    |--------------------------------------------------------------------------
    | Number of days to keep audit logs.
    | Set to null to keep forever.
    | Run: php artisan audit-trail:clean to delete old logs.
    */
    'keep_for_days' => env('AUDIT_TRAIL_KEEP_DAYS', null),

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    | Number of records to show per page on the dashboard.
    */
    'per_page' => env('AUDIT_TRAIL_PER_PAGE', 20),

    /*
    |--------------------------------------------------------------------------
    | Dashboard Title
    |--------------------------------------------------------------------------
    | Title shown on the audit trail dashboard.
    */
    'dashboard_title' => env('AUDIT_TRAIL_TITLE', 'Audit Trail'),

];
