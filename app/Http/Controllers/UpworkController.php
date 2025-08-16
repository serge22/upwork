<?php

namespace App\Http\Controllers;

// use App\Jobs\FindMatchingUsers;
use Illuminate\Http\Request;
use App\Services\UpworkService;
use App\Models\UpworkJob;
use Illuminate\Database\UniqueConstraintViolationException;

class UpworkController extends Controller
{
    public function pull()
    {
        $upwork = app(UpworkService::class);
        $jobs = $upwork->searchJobs([
            'limit' => 50,
        ]);

        foreach ($jobs['jobs'] as $i => $j) {
            try {
                $job = UpworkJob::createFromUpworkArray($j);

                // FindMatchingUsers::dispatch($job->id);
            } catch (UniqueConstraintViolationException $e) {
                break;
            }
        }

        return $i . ' jobs imported';
    }
}
