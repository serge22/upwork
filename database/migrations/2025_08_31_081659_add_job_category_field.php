<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('upwork_jobs', function (Blueprint $table) {
            $table->string('category', 50)->after('description');
            $table->string('subcategory', 50)->after('category');
            $table->string('hourlyBudgetType')->nullable()->default(null)->after('amount');
            $table->decimal('hourlyBudgetMin', 4, 1)->nullable()->default(null)->after('hourlyBudgetType');
            $table->decimal('hourlyBudgetMax', 4, 1)->nullable()->default(null)->after('hourlyBudgetMin');
            $table->boolean('premium')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upwork_jobs', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->dropColumn('subcategory');
            $table->dropColumn('hourlyBudgetType');
            $table->dropColumn('hourlyBudgetMin');
            $table->dropColumn('hourlyBudgetMax');
            $table->dropColumn('premium');
        });
    }
};
