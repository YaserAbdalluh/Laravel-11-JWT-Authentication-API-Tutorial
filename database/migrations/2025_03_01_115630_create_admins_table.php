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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable();
            $table->boolean('can_manage_users')->default(false);
            $table->boolean('can_manage_offers')->default(false);
            $table->boolean('can_manage_orders')->default(false);
            $table->boolean('can_manage_reviews')->default(false);
            $table->boolean('can_manage_notifications')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
