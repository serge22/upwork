<?php

namespace Database\Seeders;

use App\Models\UpworkCategory;
use App\Services\UpworkService;
use Illuminate\Database\Seeder;

class UpworkCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $upwork = app(UpworkService::class);
        $categories = $upwork->getCategories();

        foreach ($categories as $category) {
            // Create parent category
            UpworkCategory::create([
                'id' => $category['id'],
                'label' => $category['preferredLabel'],
            ]);

            // Create subcategories
            foreach ($category['subcategories'] as $child) {
                UpworkCategory::create([
                    'id' => $child['id'],
                    'parent_id' => $category['id'],
                    'label' => $child['preferredLabel'],
                ]);
            }
        }
    }
}
