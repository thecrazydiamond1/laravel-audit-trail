# Laravel Audit Trail

A powerful, zero-configuration audit trail package for Laravel. Automatically tracks every create, update, and delete on your Eloquent models with a beautiful built-in dashboard.

## Features

- Automatic model change tracking — just add one trait
- Records old and new values for every change
- Captures who made the change, from which IP, and when
- Beautiful built-in dashboard at `/audit-trail`
- One-click revert to any previous state
- Expandable diff view for changes
- Filterable by model, action, user, date range
- Fully configurable via config file
- Excludes sensitive fields like passwords automatically
- Works with any model — User, Invoice, Order, anything

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x

## Installation

Install via Composer:

```bash
composer require jeevanjoshi/laravel-audit-trail
```

The package will be auto-discovered by Laravel. No manual configuration needed.

## Setup

### 1. Publish and run the migration

```bash
php artisan vendor:publish --tag=audit-trail-migrations
php artisan migrate
```

This creates the `audit_trails` table in your database.

### 2. Add the trait to any model you want to track

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jeevanjoshi\LaravelAuditTrail\Traits\HasAuditTrail;

class User extends Model
{
    use HasAuditTrail;
}
```

That's it. Every create, update, and delete on this model is now tracked automatically.

## Dashboard

Visit the audit trail dashboard at:
https://your-app.com/audit-trail

The dashboard shows:
- Total changes, today's changes, deletions, active users
- Full change history with old and new values
- Filters by model, action, model ID, and date range
- Revert button to restore any previous state

By default the dashboard is protected by `auth` middleware. Make sure your app has authentication set up.

## Querying Audit History

```php
// Get all audit history for a specific record
$user->audits()->get();

// Get only updates
$user->audits()->ofAction('updated')->get();

// Get latest audit
$user->latestAudit();

// Check if model has any history
$user->hasAuditHistory();

// Get paginated history
$user->audits()->latest()->paginate(20);
```

## Reverting Changes

```php
// Revert a record to its previous state
$audit = $user->audits()->latest()->first();
$user->revertTo($audit->id);

// Returns true on success, false on failure
```

## Silent Updates (without logging)

```php
// This update will NOT be logged
$user->withoutAudit(function () use ($user) {
    $user->update(['last_seen_at' => now()]);
});
```

Useful for updates that aren't meaningful — like updating timestamps, counters, or cache fields.

## Excluding Fields Per Model

```php
class User extends Model
{
    use HasAuditTrail;

    // These fields will never be logged for this model
    protected array $auditExclude = [
        'two_factor_secret',
        'last_seen_at',
    ];
}
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=audit-trail-config
```

This creates `config/audit-trail.php` in your application:

```php
return [
    // Enable or disable the package entirely
    'enabled' => env('AUDIT_TRAIL_ENABLED', true),

    // Your user model
    'user_model' => env('AUDIT_TRAIL_USER_MODEL', 'App\Models\User'),

    // Dashboard URL prefix
    // Default: your-app.com/audit-trail
    'route_prefix' => env('AUDIT_TRAIL_ROUTE_PREFIX', 'audit-trail'),

    // Middleware protecting the dashboard
    'middleware' => ['web', 'auth'],

    // Fields never logged across ALL models
    'exclude_fields' => [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'updated_at',
    ],

    // Auto delete logs older than X days (null = keep forever)
    'keep_for_days' => env('AUDIT_TRAIL_KEEP_DAYS', null),

    // Records per page on dashboard
    'per_page' => env('AUDIT_TRAIL_PER_PAGE', 20),

    // Dashboard title
    'dashboard_title' => env('AUDIT_TRAIL_TITLE', 'Audit Trail'),
];
```

## Customizing the Dashboard UI

Publish the views to customize them:

```bash
php artisan vendor:publish --tag=audit-trail-views
```

This copies the views to `resources/views/vendor/audit-trail/` in your application. Edit them freely — they won't be overwritten when you update the package.

## Environment Variables

Add these to your `.env` to configure without touching the config file:

```env
AUDIT_TRAIL_ENABLED=true
AUDIT_TRAIL_ROUTE_PREFIX=audit-trail
AUDIT_TRAIL_KEEP_DAYS=90
AUDIT_TRAIL_PER_PAGE=20
AUDIT_TRAIL_TITLE=Audit Trail
```

## Real World Use Cases

- **Hospital systems** — track who changed patient records and when
- **Financial apps** — full audit log of every transaction change
- **SaaS apps** — show teams what changed in their account
- **eCommerce** — track order and inventory changes
- **HR systems** — log every change to employee records
- **Any app** — know exactly what changed, who changed it, and when

## Available Scopes

```php
// Filter by action
Audit::ofAction('created')->get();
Audit::ofAction('updated')->get();
Audit::ofAction('deleted')->get();

// Filter by model
Audit::forModel(User::class)->get();

// Filter by user
Audit::byUser($userId)->get();

// Filter by date
Audit::today()->get();
Audit::thisMonth()->get();
```

## Security

The dashboard is protected by `auth` middleware by default. To add additional protection:

```php
// config/audit-trail.php
'middleware' => ['web', 'auth', 'can:view-audit-trail'],
```

Sensitive fields like `password` and `remember_token` are never logged regardless of configuration.

## Testing

```bash
composer test
```
## Cleaning Old Logs

Run the cleanup command manually:

```bash
php artisan audit-trail:clean
```

The command will:
- Check how many logs are older than `keep_for_days`
- Ask for confirmation before deleting
- Show how many records were deleted

## Changelog

### v1.0.0
- Initial release
- Automatic model change tracking
- Built-in dashboard with filters
- Revert to previous state
- Configurable excluded fields
- Per-model field exclusions
- Silent updates with withoutAudit()

## License

MIT License. See [LICENSE](LICENSE) for more information.

## Author

**Jeevan Joshi**
- GitHub: [@thecrazydiamond1](https://github.com/thecrazydiamond1)
- Email: joshijeewon18@gmail.com
