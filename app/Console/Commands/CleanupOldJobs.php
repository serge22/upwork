<?php

namespace App\Console\Commands;

use App\Models\UpworkJob;
use Illuminate\Console\Command;

class CleanupOldJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-old-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes Upwork job records older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of old Upwork jobs...');

        $cutoffDate = now()->subHours(24);

        $deletedCount = UpworkJob::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Deleted {$deletedCount} job(s) older than 24 hours.");

        return Command::SUCCESS;
    }
}
