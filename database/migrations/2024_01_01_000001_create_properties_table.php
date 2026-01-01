<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('type', ['apartment', 'house', 'studio', 'villa']);
            $table->decimal('price', 10, 2);
            $table->decimal('yearly_price', 10, 2)->nullable();
            $table->decimal('deposit', 10, 2)->nullable();
            $table->enum('rental_period', ['monthly', 'yearly', 'both'])->default('monthly');
            $table->boolean('utilities_included')->default(false);
            $table->string('address');
            $table->string('city');
            $table->string('district')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('bedrooms');
            $table->integer('bathrooms');
            $table->decimal('area', 8, 2);
            $table->integer('floor')->nullable();
            $table->integer('total_floors')->nullable();
            $table->enum('furnishing', ['furnished', 'semi-furnished', 'unfurnished']);
            $table->boolean('parking')->default(false);
            $table->integer('parking_spaces')->default(0);
            $table->boolean('pets_allowed')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected', 'rented'])->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('owner_id');
            $table->index('city');
            $table->index('type');
            $table->index('status');
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
