<?php

namespace App\Jobs;

use App\Models\Feed;
use App\Models\UpworkJob;
use App\Models\User;
use App\Services\TelegramNotificationService;
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
        $job = UpworkJob::with(['subcategoryModel'])->find($this->jobId);
        $telegramService = new TelegramNotificationService();

        Feed::where('is_active', '=', 1)
            ->whereHas('user', function($query) {
                $query->whereNotNull('telegram_id')->where('telegram_id', '!=', '');
            })
            ->with('user') // Eager load user relationship
            ->chunk(20, function($feeds) use ($job, $telegramService) {
                foreach ($feeds as $feed) {
                    if ($feed->matchesJob($job)) {
                        $success = $telegramService->sendJobNotification(
                            $feed->user->telegram_id,
                            $job,
                            $feed
                        );

                        if ($success) {
                            echo "Notification sent to feed: {$feed->name} (User: {$feed->user->name})\n";
                        } else {
                            echo "Failed to send notification to feed: {$feed->name} (User: {$feed->user->name})\n";
                        }
                    }
                }
            });
    }
}
