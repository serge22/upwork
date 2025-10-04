<?php

namespace App\Console\Commands;

use App\Jobs\FindMatchingUsers;
use App\Models\UpworkJob;
use App\Services\UpworkService;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;

class FetchUpworkJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-jobs {--limit=50 : Number of jobs to fetch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and process jobs from the Upwork API';

    /**
     * The Upwork service instance.
     *
     * @var UpworkService
     */
    private $upwork;

    /**
     * Create a new command instance.
     *
     * @param UpworkService $upwork
     */
    public function __construct(UpworkService $upwork)
    {
        parent::__construct();
        $this->upwork = $upwork;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $this->info("Fetching up to {$limit} jobs from Upwork...");

        try {
            $jobs = $this->upwork->searchJobs(['limit' => $limit]);

            if (!isset($jobs['jobs']) || !is_array($jobs['jobs'])) {
                $this->error('Invalid response from Upwork API.');
                return;
            }

            foreach ($jobs['jobs'] as $jobData) {
                try {
                    $job = UpworkJob::createFromUpworkArray($jobData);
                    FindMatchingUsers::dispatch($job->id);
                    $this->info("Processed job ID: {$job->id}");
                } catch (UniqueConstraintViolationException $e) {
                    // stop importing if we hit a duplicate
                    break;
                } catch (\Exception $e) {
                    Log::error("Failed to process job: {$e->getMessage()}");
                }
            }

            $this->info('Job fetching and processing completed.');
        } catch (\Exception $e) {
            $this->error("Error fetching jobs: {$e->getMessage()}");
            Log::error("Upwork API error: {$e->getMessage()}");
        }
    }
}
