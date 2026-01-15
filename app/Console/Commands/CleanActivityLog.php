<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\ActivityLog\Models\Activity;

class CleanActivityLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-activity-log {--days=90 : Number of days to keep activity logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove activity log entries older than specified days to prevent database bloat';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $date = now()->subDays($days);
        
        $deletedCount = Activity::where('created_at', '<', $date)->delete();
        
        $this->info("✅ Deleted {$deletedCount} activity log entries older than {$days} days");
        
        return 0;
    }
}
