<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if column exists before adding
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['vendor', 'processor', 'admin'])->default('vendor')->after('email');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'business_name')) {
                $table->string('business_name')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'market_area')) {
                $table->string('market_area')->nullable()->after('business_name');
            }
            if (!Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('market_area');
            }
            if (!Schema::hasColumn('users', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('users', 'rating_avg')) {
                $table->decimal('rating_avg', 3, 2)->default(0)->after('longitude');
            }
            if (!Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('rating_avg');
            }
            if (!Schema::hasColumn('users', 'service_radius')) {
                $table->integer('service_radius')->nullable()->after('is_verified');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'phone',
                'business_name',
                'market_area',
                'latitude',
                'longitude',
                'rating_avg',
                'is_verified',
                'service_radius'
            ]);
        });
    }
};