<?php

namespace Jeevanjoshi\LaravelAuditTrail;

use Illuminate\Support\ServiceProvider;
use Jeevanjoshi\LaravelAuditTrail\Commands\CleanAuditTrailCommand;


class AuditTrailServiceProvider extends ServiceProvider
{
    /**
     * Register services into the container
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/audit-trail.php',
            'audit-trail'
        );
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerRoutes();
        $this->registerViews();
        $this->registerCommands();
    }

    /**
     * Register publishable files
     */
    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {

            // Publish config
            $this->publishes([
                __DIR__ . '/../config/audit-trail.php'
                    => config_path('audit-trail.php'),
            ], 'audit-trail-config');

            // Publish migrations
            $this->publishes([
                __DIR__ . '/database/migrations'
                    => database_path('migrations'),
            ], 'audit-trail-migrations');

            // Publish views
            $this->publishes([
                __DIR__ . '/../resources/views'
                    => resource_path('views/vendor/audit-trail'),
            ], 'audit-trail-views');
        }
    }

    /**
     * Register package routes
     */
    private function registerRoutes(): void
    {
        if (config('audit-trail.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }
    }

    /**
     * Register artisan commands
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanAuditTrailCommand::class,
            ]);
        }
    }

    /**
     * Register package views
     */
    private function registerViews(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'audit-trail'
        );
    }
}
