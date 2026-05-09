<?php

namespace Jeevanjoshi\LaravelAuditTrail\Commands;

use Illuminate\Console\Command;
use Jeevanjoshi\LaravelAuditTrail\Models\Audit;

class CleanAuditTrailCommand extends Command
{
    protected $signature   = 'audit-trail:clean';
    protected $description = 'Delete audit logs older than the configured keep_for_days';

    public function handle(): void
    {
        $days = config('audit-trail.keep_for_days');

        if (is_null($days)) {
            $this->info('audit-trail.keep_for_days is not set. Nothing to clean.');
            return;
        }

        if (!is_numeric($days) || $days < 1) {
            $this->error('audit-trail.keep_for_days must be a positive number.');
            return;
        }

        $cutoff = now()->subDays($days);

        $count = Audit::where('created_at', '<', $cutoff)->count();

        if ($count === 0) {
            $this->info("No audit logs older than {$days} days found.");
            return;
        }

        $this->info("Found {$count} audit logs older than {$days} days.");

        if ($this->confirm("Delete these {$count} records permanently?")) {
            Audit::where('created_at', '<', $cutoff)->delete();
            $this->info("✓ Successfully deleted {$count} old audit logs.");
        } else {
            $this->info('Cleanup cancelled.');
        }
    }
}
