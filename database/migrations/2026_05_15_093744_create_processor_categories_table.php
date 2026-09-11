<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('processor_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('waste_categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processor_categories');
    }
};