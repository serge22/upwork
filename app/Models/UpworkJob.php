<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpworkJob extends Model
{
    protected $fillable = [
        'id',
        'title',
        'description',
        'upwork_category_id',
        'ciphertext',
        'duration',
        'engagement',
        'amount',
        'hourlyBudgetType',
        'hourlyBudgetMin',
        'hourlyBudgetMax',
        'premium',
        'experience',
        'client_hires',
        'client_jobs',
        'client_spent',
        'client_verified',
        'client_country',
        'client_reviews',
        'client_feedback',
        'created_at',
        'updated_at',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function category(): BelongsTo
    {
        return $this->belongsTo(UpworkCategory::class, 'upwork_category_id');
    }


    /**
     * Create a new instance from an array and save it.
     *
     * @param array $data
     * @return mixed
     */
    public static function createFromUpworkArray(array $data)
    {
        // Find the category ID by slug
        $upworkCategoryId = null;
        if (isset($data['subcategory'])) {
            $category = UpworkCategory::where('slug', $data['subcategory'])->first();
            $upworkCategoryId = $category ? $category->id : null;
        }

        return self::create([
            'id' => $data['id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'upwork_category_id' => $upworkCategoryId,
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
