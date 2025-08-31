<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UpworkCategory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'parent_id',
        'label',
        'slug'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->label, '_');
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('label') && empty($category->slug)) {
                $category->slug = Str::slug($category->label, '_');
            }
        });
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(UpworkCategory::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(UpworkCategory::class, 'parent_id');
    }
}
