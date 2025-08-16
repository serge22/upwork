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
        Schema::create('upwork_jobs', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('title');
            $table->text('description');
            $table->string('ciphertext', 22);
            $table->string('duration')->nullable();
            $table->string('engagement')->nullable();
            $table->float('amount', 2);
            $table->string('experience');

            $table->unsignedSmallInteger('client_hires');
            $table->unsignedSmallInteger('client_jobs');
            $table->float('client_spent', 2)->nullable();
            $table->boolean('client_verified');
            $table->string('client_country')->nullable();
            $table->unsignedSmallInteger('client_reviews');
            $table->float('client_feedback', 2);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upwork_jobs');
    }
};
