<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('waste_listings', function (Blueprint $table) {
            if (!Schema::hasColumn('waste_listings', 'unit')) {
                $table->string('unit')->default('kg')->after('quantity_kg');
            }
            if (!Schema::hasColumn('waste_listings', 'waste_type_name')) {
                $table->string('waste_type_name')->after('waste_type_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('waste_listings', function (Blueprint $table) {
            $table->dropColumn(['unit', 'waste_type_name']);
        });
    }
};