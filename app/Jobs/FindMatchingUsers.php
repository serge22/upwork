<?php

namespace App\Jobs;

use App\Models\Feed;
use App\Models\UpworkJob;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FindMatchingUsers implements ShouldQueue
{
    use Queueable;

    protected $jobId;

    /**
     * Create a new job instance.
     */
    public function __construct($jobId)
    {
        $this->jobId = $jobId;
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = UpworkJob::find($this->jobId);

        Feed::where('is_active', '=', 1)
            ->whereHas('user', function($query) {
                $query->whereNotNull('telegram_id')->where('telegram_id', '!=', '');
            })
            ->chunk(20, function($feeds) use ($job) {
                foreach ($feeds as $f) {
                    if ($f->matchesJob($job)) {
                        // TODO: Send notification to user

                        echo "Notification sent to feed: {$f->name}\n";
                    }
                }
            });
    }
}
