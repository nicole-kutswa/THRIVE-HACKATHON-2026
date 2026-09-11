<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('waste_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('waste_categories')->onDelete('set null');
            $table->foreignId('waste_type_id')->constrained('waste_types')->onDelete('cascade');
            $table->string('waste_type_name');
            $table->decimal('quantity_kg', 10, 2);
            $table->string('market_area');
            $table->string('availability_window');
            $table->string('photo_url')->nullable();
            $table->enum('status', ['active', 'claimed', 'collected', 'completed', 'expired', 'cancelled'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_listings');
    }
};