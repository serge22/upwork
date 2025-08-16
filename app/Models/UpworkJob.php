<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UpworkJob extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';


    /**
     * Create a new instance from an array and save it.
     *
     * @param array $data
     * @return mixed
     */
    public static function createFromUpworkArray(array $data)
    {
        return self::create([
            'id' => $data['id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'ciphertext' => $data['ciphertext'],
            'duration' => $data['durationLabel'],
            'engagement' => $data['engagement'],
            'amount' => $data['amount']['displayValue'] ?? null,
            'experience' => $data['experienceLevel'],

            'client_hires' => $data['client']['totalHires'],
            'client_jobs' => $data['client']['totalPostedJobs'],
            'client_spent' => $data['client']['totalSpent']['displayValue'] ?? null,
            'client_verified' => $data['client']['verificationStatus'] === 'VERIFIED',
            'client_country' => $data['client']['location']['country'] ?? null,
            'client_reviews' => $data['client']['totalReviews'],
            'client_feedback' => $data['client']['totalFeedback'],

            'created_at' => $data['createdDateTime'],
        ]);
    }
}
