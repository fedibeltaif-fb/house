# 🔧 Laravel Code Snippets - Ready to Use

> **Quick reference for common Laravel patterns used in the rental platform**

---

## 📦 Models

### Property Model (Complete)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id', 'title', 'slug', 'description', 'type',
        'price', 'yearly_price', 'deposit', 'rental_period',
        'utilities_included', 'address', 'city', 'district',
        'postal_code', 'latitude', 'longitude', 'bedrooms',
        'bathrooms', 'area', 'floor', 'total_floors',
        'furnishing', 'parking', 'parking_spaces', 'pets_allowed',
        'status', 'is_featured', 'featured_until', 'is_active',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'deposit' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'area' => 'decimal:2',
        'parking' => 'boolean',
        'pets_allowed' => 'boolean',
        'utilities_included' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'featured_until' => 'datetime',
        'published_at' => 'datetime',
        'average_rating' => 'decimal:2',
    ];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class)->orderBy('order');
    }

    public function primaryImage()
    {
        return $this->hasOne(PropertyImage::class)->where('is_primary', true);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
            ->where('featured_until', '>', now());
    }

    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopePriceBetween($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    // Accessors & Mutators
    public function getPriceFormattedAttribute()
    {
        return '$' . number_format($this->price, 0);
    }

    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->city}";
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            if (empty($property->slug)) {
                $property->slug = Str::slug($property->title);
            }
        });

        static::updating(function ($property) {
            if ($property->isDirty('title')) {
                $property->slug = Str::slug($property->title);
            }
        });
    }
}
```

---

## 🗄️ Migrations

### Properties Table Migration

```php
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

            // Property Type
            $table->enum('type', ['apartment', 'house', 'studio', 'villa']);

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('yearly_price', 10, 2)->nullable();
            $table->decimal('deposit', 10, 2)->nullable();
            $table->enum('rental_period', ['monthly', 'yearly', 'both'])->default('monthly');
            $table->boolean('utilities_included')->default(false);

            // Location
            $table->string('address');
            $table->string('city');
            $table->string('district')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Property Details
            $table->integer('bedrooms');
            $table->integer('bathrooms');
            $table->decimal('area', 8, 2);
            $table->integer('floor')->nullable();
            $table->integer('total_floors')->nullable();

            // Features
            $table->enum('furnishing', ['furnished', 'semi-furnished', 'unfurnished']);
            $table->boolean('parking')->default(false);
            $table->integer('parking_spaces')->default(0);
            $table->boolean('pets_allowed')->default(false);

            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'rented'])->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->boolean('is_active')->default(true);

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Stats
            $table->integer('views_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('owner_id');
            $table->index('city');
            $table->index('type');
            $table->index('status');
            $table->index('price');
            $table->index(['latitude', 'longitude']);
            $table->fullText(['title', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
```

---

## 🎮 Controllers

### Property Controller (Simplified)

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(
        private PropertyService $propertyService
    ) {
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index()
    {
        $properties = Property::with(['owner', 'images'])
            ->approved()
            ->active()
            ->latest()
            ->paginate(15);

        $featured = Property::featured()
            ->with(['owner', 'images'])
            ->limit(6)
            ->get();

        return view('properties.index', compact('properties', 'featured'));
    }

    public function show(string $slug)
    {
        $property = Property::with(['owner', 'images', 'amenities', 'reviews.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('view', $property);

        // Increment views
        $property->increment('views_count');

        // Get similar properties
        $similar = Property::approved()
            ->active()
            ->where('id', '!=', $property->id)
            ->where('city', $property->city)
            ->where('type', $property->type)
            ->whereBetween('price', [$property->price * 0.8, $property->price * 1.2])
            ->limit(6)
            ->get();

        return view('properties.show', compact('property', 'similar'));
    }

    public function create()
    {
        $this->authorize('create', Property::class);

        $amenities = \App\Models\Amenity::all();
        $cities = config('cities');

        return view('properties.create', compact('amenities', 'cities'));
    }

    public function store(CreatePropertyRequest $request)
    {
        $data = $request->validated();
        $data['owner_id'] = auth()->id();

        $property = $this->propertyService->createProperty(
            $data,
            $request->file('images', [])
        );

        return redirect()
            ->route('properties.show', $property->slug)
            ->with('success', 'Property submitted for review!');
    }

    public function edit(Property $property)
    {
        $this->authorize('update', $property);

        $amenities = \App\Models\Amenity::all();
        $cities = config('cities');

        return view('properties.edit', compact('property', 'amenities', 'cities'));
    }

    public function update(UpdatePropertyRequest $request, Property $property)
    {
        $this->authorize('update', $property);

        $property = $this->propertyService->updateProperty(
            $property->id,
            $request->validated(),
            $request->file('new_images', [])
        );

        return redirect()
            ->route('properties.show', $property->slug)
            ->with('success', 'Property updated successfully!');
    }

    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);

        $this->propertyService->deleteProperty($property->id);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Property deleted successfully');
    }
}
```

---

## 🔍 Search Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Property;
use App\Models\Amenity;

class SearchController extends Controller
{
    public function index(SearchRequest $request)
    {
        $filters = $request->validated();

        $query = Property::query()
            ->with(['owner', 'images'])
            ->approved()
            ->active();

        // Apply filters
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', $filters['bedrooms']);
        }

        if (!empty($filters['amenities'])) {
            $query->whereHas('amenities', function ($q) use ($filters) {
                $q->whereIn('amenities.id', $filters['amenities']);
            }, '=', count($filters['amenities']));
        }

        // Sorting
        switch ($filters['sort_by'] ?? 'latest') {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            default:
                $query->latest();
        }

        $properties = $query->paginate(15)->withQueryString();

        $amenities = Amenity::all();
        $cities = config('cities');

        return view('properties.search', compact('properties', 'amenities', 'cities', 'filters'));
    }
}
```

---

## 🛠️ Services

### Property Service (Core Methods)

```php
<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyService
{
    public function __construct(
        private ImageService $imageService,
        private NotificationService $notificationService
    ) {}

    public function createProperty(array $data, array $images = []): Property
    {
        return DB::transaction(function () use ($data, $images) {
            // Generate slug
            $data['slug'] = Str::slug($data['title']);
            $data['status'] = 'pending';

            // Create property
            $property = Property::create($data);

            // Attach amenities
            if (!empty($data['amenities'])) {
                $property->amenities()->attach($data['amenities']);
            }

            // Upload images
            if (!empty($images)) {
                foreach ($images as $index => $image) {
                    $this->imageService->uploadPropertyImage($property, $image, $index === 0);
                }
            }

            // Notify admin
            $this->notificationService->notifyAdminNewListing($property);

            return $property->fresh(['images', 'amenities']);
        });
    }

    public function updateProperty(int $id, array $data, array $newImages = []): Property
    {
        return DB::transaction(function () use ($id, $data, $newImages) {
            $property = Property::findOrFail($id);

            // Update slug if title changed
            if (isset($data['title']) && $data['title'] !== $property->title) {
                $data['slug'] = Str::slug($data['title']);
            }

            $property->update($data);

            // Update amenities
            if (isset($data['amenities'])) {
                $property->amenities()->sync($data['amenities']);
            }

            // Add new images
            if (!empty($newImages)) {
                foreach ($newImages as $image) {
                    $this->imageService->uploadPropertyImage($property, $image);
                }
            }

            return $property->fresh(['images', 'amenities']);
        });
    }

    public function approveProperty(int $id): Property
    {
        $property = Property::findOrFail($id);

        $property->update([
            'status' => 'approved',
            'published_at' => now()
        ]);

        $this->notificationService->notifyOwnerPropertyApproved($property);
        $this->notificationService->notifyUsersNewListing($property);

        return $property->fresh();
    }
}
```

---

## 📝 Form Requests

### Create Property Request

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-property');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:50'],
            'type' => ['required', 'in:apartment,house,studio,villa'],
            'price' => ['required', 'numeric', 'min:0'],
            'rental_period' => ['required', 'in:monthly,yearly,both'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'bedrooms' => ['required', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['required', 'integer', 'min:0', 'max:20'],
            'area' => ['required', 'numeric', 'min:1'],
            'furnishing' => ['required', 'in:furnished,semi-furnished,unfurnished'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:amenities,id'],
            'images' => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'Please upload at least one image',
            'images.*.max' => 'Each image must not exceed 5MB',
            'description.min' => 'Description must be at least 50 characters',
        ];
    }
}
```

---

## 🌐 Routes

### Web Routes

```php
<?php

use App\Http\Controllers\PropertyController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Admin;

// Public routes
Route::get('/', [PropertyController::class, 'index'])->name('home');
Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{property:slug}', [PropertyController::class, 'show'])->name('properties.show');
Route::get('/search', [SearchController::class, 'index'])->name('properties.search');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Favorites
    Route::post('/favorites/{property}', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{property}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    // Reviews
    Route::post('/properties/{property}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages/{property}', [MessageController::class, 'store'])->name('messages.store');
});

// Owner routes
Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::resource('properties', PropertyController::class)->except(['index', 'show']);
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/properties', [Admin\PropertyController::class, 'index'])->name('properties.index');
    Route::post('/properties/{property}/approve', [Admin\PropertyController::class, 'approve'])->name('properties.approve');
    Route::post('/properties/{property}/reject', [Admin\PropertyController::class, 'reject'])->name('properties.reject');
});
```

---

## 🎨 Blade Components

### Property Card Component

```blade
{{-- resources/views/components/property-card.blade.php --}}
@props(['property'])

<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
    {{-- Image --}}
    <div class="relative h-48">
        <img src="{{ $property->primaryImage?->image_path ?? '/images/placeholder.jpg' }}"
             alt="{{ $property->title }}"
             class="w-full h-full object-cover">

        @if($property->is_featured)
            <span class="absolute top-2 left-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded">
                Featured
            </span>
        @endif

        <x-favorite-button :property="$property" class="absolute top-2 right-2" />
    </div>

    {{-- Content --}}
    <div class="p-4">
        <h3 class="text-lg font-bold mb-2 truncate">
            <a href="{{ route('properties.show', $property->slug) }}" class="hover:text-blue-600">
                {{ $property->title }}
            </a>
        </h3>

        <p class="text-gray-600 text-sm mb-2">
            📍 {{ $property->city }}
        </p>

        <div class="flex items-center justify-between mb-3">
            <span class="text-2xl font-bold text-blue-600">
                ${{ number_format($property->price) }}<span class="text-sm text-gray-500">/mo</span>
            </span>

            @if($property->average_rating > 0)
                <div class="flex items-center">
                    <span class="text-yellow-400">⭐</span>
                    <span class="text-sm ml-1">{{ number_format($property->average_rating, 1) }}</span>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 text-sm text-gray-600">
            <span>🛏️ {{ $property->bedrooms }} BD</span>
            <span>🛁 {{ $property->bathrooms }} BA</span>
            <span>📐 {{ number_format($property->area) }} m²</span>
        </div>
    </div>
</div>
```

---

## 🔧 Configuration

### Cities Config

```php
<?php

// config/cities.php
return [
    'New York',
    'Los Angeles',
    'Chicago',
    'Houston',
    'Phoenix',
    'Philadelphia',
    'San Antonio',
    'San Diego',
    'Dallas',
    'San Jose',
];
```

---

## 🧪 Factory

### Property Factory

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(3, true),
            'type' => $this->faker->randomElement(['apartment', 'house', 'studio', 'villa']),
            'price' => $this->faker->numberBetween(500, 5000),
            'rental_period' => 'monthly',
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'bedrooms' => $this->faker->numberBetween(1, 5),
            'bathrooms' => $this->faker->numberBetween(1, 3),
            'area' => $this->faker->numberBetween(400, 2000),
            'furnishing' => $this->faker->randomElement(['furnished', 'semi-furnished', 'unfurnished']),
            'status' => 'approved',
            'is_active' => true,
        ];
    }
}
```

---

**These snippets are production-ready and can be used directly in your Laravel application! 🚀**
