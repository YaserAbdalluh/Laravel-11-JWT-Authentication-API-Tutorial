<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('profession_id')->nullable();
            $table->json('certifications')->nullable();
            $table->json('skills')->nullable();
            $table->string('experience_years')->nullable();
            $table->integer('total_reviews')->nullable();
            $table->decimal('hourly_rate')->nullable();
            $table->decimal('rating_avg')->nullable();
            $table->enum('availability_status', ['Available', 'Busy', 'Offline']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
