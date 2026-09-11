<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_listing_id')->constrained('waste_listings')->onDelete('cascade');
            $table->foreignId('processor_id')->constrained('users')->onDelete('cascade');
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->enum('status', ['pending', 'accepted', 'in_transit', 'collected', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('actual_quantity_kg', 10, 2)->nullable();
            $table->boolean('issue_reported')->default(false);
            $table->text('issue_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};