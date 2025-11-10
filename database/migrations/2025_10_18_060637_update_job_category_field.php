<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\UpworkCategory;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update subcategory values from slugs to IDs
        $jobs = DB::table('upwork_jobs')->get();
        
        foreach ($jobs as $job) {
            if ($job->subcategory) {
                $category = UpworkCategory::where('slug', $job->subcategory)->first();
                if ($category) {
                    DB::table('upwork_jobs')
                        ->where('id', $job->id)
                        ->update(['subcategory' => $category->id]);
                }
            }
        }

        // Now modify the table structure
        Schema::table('upwork_jobs', function (Blueprint $table) {
            // Drop the category column
            $table->dropColumn('category');
            
            // Change subcategory column type to unsigned big integer
            $table->unsignedBigInteger('subcategory')->nullable()->change();
            
            // Rename subcategory to upwork_category_id
            $table->renameColumn('subcategory', 'upwork_category_id');
        });

        // Add foreign key constraint
        Schema::table('upwork_jobs', function (Blueprint $table) {
            $table->foreign('upwork_category_id')->references('id')->on('upwork_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upwork_jobs', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['upwork_category_id']);
            
            // Rename back to subcategory
            $table->renameColumn('upwork_category_id', 'subcategory');
            
            // Change back to string
            $table->string('subcategory', 50)->change();
            
            // Add back category column
            $table->string('category', 50)->after('description');
        });

        // Update subcategory values back from IDs to slugs
        $jobs = DB::table('upwork_jobs')->get();
        
        foreach ($jobs as $job) {
            if ($job->subcategory) {
                $category = UpworkCategory::find($job->subcategory);
                if ($category) {
                    DB::table('upwork_jobs')
                        ->where('id', $job->id)
                        ->update(['subcategory' => $category->slug]);
                }
            }
        }
    }
};
