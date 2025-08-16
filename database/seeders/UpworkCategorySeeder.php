<?php

namespace Database\Seeders;

use App\Services\UpworkService;
use Illuminate\Support\Facades\DB;
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
        foreach ($categories as $category)
        {
            DB::table('upwork_categories')->insert([
                'id' => $category['id'],
                'label' => $category['preferredLabel'],
            ]);

            foreach ($category['subcategories'] as $child)
            {
                DB::table('upwork_categories')->insert([
                    'id' => $child['id'],
                    'parent_id' => $category['id'],
                    'label' => $child['preferredLabel'],
                ]);
            }
        }
    }
}
