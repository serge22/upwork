<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UpworkJob extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    public function categoryModel()
    {
        return $this->belongsTo(UpworkCategory::class, 'category', 'slug');
    }

    public function subcategoryModel()
    {
        return $this->belongsTo(UpworkCategory::class, 'subcategory', 'slug');
    }


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
            'category' => $data['category'],
            'subcategory' => $data['subcategory'],
            'ciphertext' => $data['ciphertext'],
            'duration' => $data['durationLabel'],
            'engagement' => $data['engagement'],
            'amount' => $data['amount']['displayValue'] ?? null,
            'hourlyBudgetType' => $data['hourlyBudgetType'],
            'hourlyBudgetMin' => $data['hourlyBudgetMin']['displayValue'] ?? null,
            'hourlyBudgetMax' => $data['hourlyBudgetMax']['displayValue'] ?? null,
            'premium' => $data['premium'],
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
