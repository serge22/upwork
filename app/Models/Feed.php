<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feed extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'search_query',
        'is_active'
    ];

    protected $casts = [
        'search_query' => 'array',
        'is_active' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matchesJob(UpworkJob $job): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // If no search rules defined, match all jobs
        if (empty($this->search_query)) {
            return true;
        }


        if ($this->search_query) {
            foreach ($this->search_query as $rule) {
                if ( ! $this->evaluateRule($job, $rule) ) {
                    return false; // Short-circuit on first non-match
                }
            }
        }

        return true;
    }

    private function evaluateRule(UpworkJob $job, array $rule): bool
    {
        $keywords = $rule['keywords'] ?? [];
        $locations = $rule['location'] ?? ['description'];
        $condition = $rule['condition'] ?? 'any'; // 'any', 'all', 'none'

        if (empty($keywords)) {
            return true;
        }

        $searchTexts = $this->getSearchTexts($job, $locations);
        $matches = 0;

        foreach ($keywords as $keyword) {
            $keywordFound = false;
            foreach ($searchTexts as $text) {
                if (str_contains(strtolower($text), strtolower($keyword))) {
                    $keywordFound = true;
                    break;
                }
            }
            if ($keywordFound) {
                $matches++;
                if ($condition === 'any') {
                    return true;
                }
            }
        }

        return match($condition) {
            'all' => $matches === count($keywords),
            'any' => $matches > 0,
            'none' => $matches === 0,
            default => false
        };
    }

    private function getSearchTexts(UpworkJob $job, array $locations): array
    {
        $texts = [];

        foreach ($locations as $location) {
            match($location) {
                'title' => $texts[] = $job->title,
                'description' => $texts[] = $job->description,
                'category' => $texts[] = $job->category,
                default => null
            };
        }

        return array_filter($texts);
    }
}
