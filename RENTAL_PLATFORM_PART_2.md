# 🏠 Production-Ready Rental Property Platform - Complete Laravel Guide (Part 2)

> **Continuation: Advanced Features, Implementation, and Production Deployment**

---

## 1️⃣1️⃣ Map Integration

### 🗺️ Mapbox Integration

#### Installation

```bash
npm install mapbox-gl
```

#### Configuration

```php
// config/services.php
return [
    // ...
    'mapbox' => [
        'token' => env('MAPBOX_TOKEN'),
        'style' => env('MAPBOX_STYLE', 'mapbox://styles/mapbox/streets-v12'),
    ],
];
```

```env
# .env
MAPBOX_TOKEN=your_mapbox_token_here
```

#### Geocoding Service

```php
// app/Services/GeocodingService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeocodingService
{
    private string $token;

    public function __construct()
    {
        $this->token = config('services.mapbox.token');
    }

    public function geocode(string $address): ?array
    {
        $response = Http::get('https://api.mapbox.com/geocoding/v5/mapbox.places/' . urlencode($address) . '.json', [
            'access_token' => $this->token,
            'limit' => 1,
        ]);

        if ($response->successful() && !empty($response->json('features'))) {
            $feature = $response->json('features')[0];

            return [
                'latitude' => $feature['center'][1],
                'longitude' => $feature['center'][0],
                'formatted_address' => $feature['place_name'],
            ];
        }

        return null;
    }

    public function reverseGeocode(float $latitude, float $longitude): ?array
    {
        $response = Http::get("https://api.mapbox.com/geocoding/v5/mapbox.places/{$longitude},{$latitude}.json", [
            'access_token' => $this->token,
        ]);

        if ($response->successful() && !empty($response->json('features'))) {
            $feature = $response->json('features')[0];

            return [
                'address' => $feature['place_name'],
                'city' => $this->extractCity($feature),
                'postal_code' => $this->extractPostalCode($feature),
            ];
        }

        return null;
    }

    private function extractCity(array $feature): ?string
    {
        foreach ($feature['context'] ?? [] as $context) {
            if (str_starts_with($context['id'], 'place.')) {
                return $context['text'];
            }
        }
        return null;
    }

    private function extractPostalCode(array $feature): ?string
    {
        foreach ($feature['context'] ?? [] as $context) {
            if (str_starts_with($context['id'], 'postcode.')) {
                return $context['text'];
            }
        }
        return null;
    }
}
```

#### Map Component (Blade + Alpine.js)

```blade
{{-- resources/views/components/map.blade.php --}}
@props(['latitude', 'longitude', 'zoom' => 14, 'interactive' => true])

<div x-data="mapComponent({{ $latitude }}, {{ $longitude }}, {{ $zoom }}, {{ $interactive ? 'true' : 'false' }})"
     x-init="initMap()"
     {{ $attributes->merge(['class' => 'w-full h-96 rounded-lg']) }}
     x-ref="mapContainer">
</div>

@push('scripts')
<link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />
<script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>

<script>
function mapComponent(lat, lng, zoom, interactive) {
    return {
        map: null,
        marker: null,

        initMap() {
            mapboxgl.accessToken = '{{ config('services.mapbox.token') }}';

            this.map = new mapboxgl.Map({
                container: this.$refs.mapContainer,
                style: '{{ config('services.mapbox.style') }}',
                center: [lng, lat],
                zoom: zoom,
                interactive: interactive
            });

            // Add marker
            this.marker = new mapboxgl.Marker()
                .setLngLat([lng, lat])
                .addTo(this.map);

            // Add navigation controls if interactive
            if (interactive) {
                this.map.addControl(new mapboxgl.NavigationControl());
            }
        }
    }
}
</script>
@endpush
```

#### Usage in Property Detail Page

```blade
{{-- resources/views/properties/show.blade.php --}}
<div class="mt-8">
    <h3 class="text-2xl font-bold mb-4">Location</h3>
    <x-map
        :latitude="$property->latitude"
        :longitude="$property->longitude"
        :zoom="15"
        class="h-96 rounded-lg shadow-md"
    />
    <p class="mt-2 text-gray-600">{{ $property->address }}, {{ $property->city }}</p>
</div>
```

---

## 1️⃣2️⃣ Image Upload & Storage

### 📸 Image Service Implementation

```php
// app/Services/ImageService.php
namespace App\Services;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageService
{
    public function uploadPropertyImages(Property $property, array $images): void
    {
        foreach ($images as $index => $image) {
            $this->uploadPropertyImage($property, $image, $index === 0);
        }
    }

    public function uploadPropertyImage(Property $property, UploadedFile $image, bool $isPrimary = false): PropertyImage
    {
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();

        // Optimize and resize image
        $optimizedImage = Image::make($image)
            ->resize(1920, 1080, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('jpg', 85);

        // Create thumbnail
        $thumbnail = Image::make($image)
            ->resize(400, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('jpg', 80);

        // Upload to storage (S3/Cloudinary/Local)
        $imagePath = "properties/{$property->id}/{$filename}";
        $thumbnailPath = "properties/{$property->id}/thumbnails/{$filename}";

        Storage::disk('public')->put($imagePath, $optimizedImage);
        Storage::disk('public')->put($thumbnailPath, $thumbnail);

        // Save to database
        return PropertyImage::create([
            'property_id' => $property->id,
            'image_path' => $imagePath,
            'thumbnail_path' => $thumbnailPath,
            'order' => $property->images()->count(),
            'is_primary' => $isPrimary,
        ]);
    }

    public function deletePropertyImages(Property $property): void
    {
        foreach ($property->images as $image) {
            $this->deleteImage($image);
        }
    }

    public function deleteImage(PropertyImage $image): void
    {
        // Delete from storage
        Storage::disk('public')->delete($image->image_path);
        Storage::disk('public')->delete($image->thumbnail_path);

        // Delete from database
        $image->delete();
    }

    public function reorderImages(Property $property, array $imageIds): void
    {
        foreach ($imageIds as $order => $imageId) {
            PropertyImage::where('id', $imageId)
                ->where('property_id', $property->id)
                ->update(['order' => $order]);
        }
    }

    public function setPrimaryImage(PropertyImage $image): void
    {
        // Remove primary flag from all images
        PropertyImage::where('property_id', $image->property_id)
            ->update(['is_primary' => false]);

        // Set new primary
        $image->update(['is_primary' => true]);
    }
}
```

### 📦 Install Intervention Image

```bash
composer require intervention/image
```

```php
// config/app.php
'providers' => [
    // ...
    Intervention\Image\ImageServiceProvider::class,
],

'aliases' => [
    // ...
    'Image' => Intervention\Image\Facades\Image::class,
],
```

### 🎨 Image Upload Component

```blade
{{-- resources/views/components/image-uploader.blade.php --}}
<div x-data="imageUploader()" class="space-y-4">
    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
        <input type="file"
               x-ref="fileInput"
               @change="handleFiles($event.target.files)"
               multiple
               accept="image/*"
               class="hidden">

        <button type="button"
                @click="$refs.fileInput.click()"
                class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700">
            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Upload Images
        </button>

        <p class="mt-2 text-sm text-gray-500">
            Upload up to 20 images (Max 5MB each)
        </p>
    </div>

    {{-- Preview Grid --}}
    <div x-show="images.length > 0"
         x-transition
         class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <template x-for="(image, index) in images" :key="index">
            <div class="relative group">
                <img :src="image.preview"
                     :alt="'Image ' + (index + 1)"
                     class="w-full h-32 object-cover rounded-lg">

                {{-- Primary Badge --}}
                <div x-show="index === 0"
                     class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">
                    Primary
                </div>

                {{-- Remove Button --}}
                <button type="button"
                        @click="removeImage(index)"
                        class="absolute top-2 right-2 bg-red-600 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    {{-- Hidden inputs for form submission --}}
    <template x-for="(image, index) in images" :key="index">
        <input type="hidden" :name="'images[' + index + ']'" :value="image.file">
    </template>
</div>

@push('scripts')
<script>
function imageUploader() {
    return {
        images: [],
        maxFiles: 20,
        maxSize: 5 * 1024 * 1024, // 5MB

        handleFiles(files) {
            const fileArray = Array.from(files);

            if (this.images.length + fileArray.length > this.maxFiles) {
                alert(`You can only upload up to ${this.maxFiles} images`);
                return;
            }

            fileArray.forEach(file => {
                if (file.size > this.maxSize) {
                    alert(`${file.name} is too large. Max size is 5MB`);
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    alert(`${file.name} is not an image`);
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.images.push({
                        file: file,
                        preview: e.target.result
                    });
                };
                reader.readAsDataURL(file);
            });
        },

        removeImage(index) {
            this.images.splice(index, 1);
        }
    }
}
</script>
@endpush
```

---

## 1️⃣3️⃣ Favorites System

### ⭐ Favorite Model

```php
// app/Models/Favorite.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    protected $fillable = ['user_id', 'property_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
```

### 📝 Update User Model

```php
// app/Models/User.php
public function favorites()
{
    return $this->belongsToMany(Property::class, 'favorites')
        ->withTimestamps();
}

public function hasFavorited(Property $property): bool
{
    return $this->favorites()->where('property_id', $property->id)->exists();
}
```

### 🎮 Favorite Controller

```php
// app/Http/Controllers/FavoriteController.php
namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $favorites = auth()->user()
            ->favorites()
            ->with(['owner', 'images'])
            ->latest('favorites.created_at')
            ->paginate(12);

        return view('favorites.index', compact('favorites'));
    }

    public function store(Property $property)
    {
        if (auth()->user()->hasFavorited($property)) {
            return response()->json([
                'message' => 'Already in favorites'
            ], 400);
        }

        auth()->user()->favorites()->attach($property->id);
        $property->increment('favorites_count');

        return response()->json([
            'message' => 'Added to favorites',
            'favorited' => true
        ]);
    }

    public function destroy(Property $property)
    {
        auth()->user()->favorites()->detach($property->id);
        $property->decrement('favorites_count');

        return response()->json([
            'message' => 'Removed from favorites',
            'favorited' => false
        ]);
    }
}
```

### 🎨 Favorite Button Component

```blade
{{-- resources/views/components/favorite-button.blade.php --}}
@props(['property'])

@auth
<div x-data="favoriteButton({{ $property->id }}, {{ auth()->user()->hasFavorited($property) ? 'true' : 'false' }})"
     {{ $attributes }}>
    <button @click="toggle()"
            :class="favorited ? 'text-red-500' : 'text-gray-400'"
            class="hover:scale-110 transition-transform">
        <svg class="w-6 h-6" :fill="favorited ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
            </path>
        </svg>
    </button>
</div>

@push('scripts')
<script>
function favoriteButton(propertyId, initialState) {
    return {
        favorited: initialState,

        async toggle() {
            try {
                const url = this.favorited
                    ? `/favorites/${propertyId}`
                    : `/favorites/${propertyId}`;

                const method = this.favorited ? 'DELETE' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();
                this.favorited = data.favorited;

            } catch (error) {
                console.error('Error:', error);
                alert('Something went wrong');
            }
        }
    }
}
</script>
@endpush
@endauth

@guest
<a href="{{ route('login') }}"
   {{ $attributes->merge(['class' => 'text-gray-400 hover:text-gray-600']) }}>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
        </path>
    </svg>
</a>
@endguest
```

---

## 1️⃣4️⃣ Reviews & Ratings

### ⭐ Review Model

```php
// app/Models/Review.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'property_id',
        'user_id',
        'rating',
        'comment',
        'owner_response',
        'owner_responded_at',
        'is_verified_renter',
        'is_approved',
    ];

    protected $casts = [
        'owner_responded_at' => 'datetime',
        'is_verified_renter' => 'boolean',
        'is_approved' => 'boolean',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
```

### 📝 Review Service

```php
// app/Services/ReviewService.php
namespace App\Services;

use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function createReview(Property $property, User $user, array $data): Review
    {
        DB::beginTransaction();

        try {
            $review = Review::create([
                'property_id' => $property->id,
                'user_id' => $user->id,
                'rating' => $data['rating'],
                'comment' => $data['comment'],
                'is_approved' => false, // Requires moderation
            ]);

            // Notify property owner
            $this->notificationService->notifyOwnerNewReview($property, $review);

            DB::commit();

            return $review;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function approveReview(Review $review): void
    {
        $review->update(['is_approved' => true]);

        // Recalculate property average rating
        $this->updatePropertyRating($review->property);

        // Notify reviewer
        $this->notificationService->notifyUserReviewApproved($review);
    }

    public function addOwnerResponse(Review $review, string $response): void
    {
        $review->update([
            'owner_response' => $response,
            'owner_responded_at' => now(),
        ]);

        // Notify reviewer
        $this->notificationService->notifyUserOwnerResponded($review);
    }

    private function updatePropertyRating(Property $property): void
    {
        $stats = $property->reviews()
            ->approved()
            ->selectRaw('AVG(rating) as average, COUNT(*) as count')
            ->first();

        $property->update([
            'average_rating' => round($stats->average, 2),
            'reviews_count' => $stats->count,
        ]);
    }
}
```

### 🎮 Review Controller

```php
// app/Http/Controllers/ReviewController.php
namespace App\Http\Controllers;

use App\Http\Requests\CreateReviewRequest;
use App\Models\Property;
use App\Models\Review;
use App\Services\ReviewService;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {
        $this->middleware('auth');
    }

    public function store(CreateReviewRequest $request, Property $property)
    {
        // Check if user already reviewed
        if ($property->reviews()->where('user_id', auth()->id())->exists()) {
            return back()->with('error', 'You have already reviewed this property');
        }

        $review = $this->reviewService->createReview(
            $property,
            auth()->user(),
            $request->validated()
        );

        return back()->with('success', 'Review submitted! It will be published after moderation.');
    }

    public function respond(Review $review, Request $request)
    {
        $this->authorize('update', $review->property);

        $request->validate([
            'response' => 'required|string|min:10|max:500'
        ]);

        $this->reviewService->addOwnerResponse($review, $request->response);

        return back()->with('success', 'Response added successfully');
    }
}
```

### 🎨 Review Display Component

```blade
{{-- resources/views/components/reviews.blade.php --}}
@props(['property'])

<div class="space-y-6">
    {{-- Rating Summary --}}
    <div class="bg-gray-50 rounded-lg p-6">
        <div class="flex items-center gap-4">
            <div class="text-center">
                <div class="text-5xl font-bold text-blue-600">
                    {{ number_format($property->average_rating, 1) }}
                </div>
                <div class="flex items-center justify-center mt-2">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= round($property->average_rating) ? 'text-yellow-400' : 'text-gray-300' }}"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $property->reviews_count }} {{ Str::plural('review', $property->reviews_count) }}
                </p>
            </div>

            <div class="flex-1">
                @foreach([5,4,3,2,1] as $star)
                    @php
                        $count = $property->reviews()->approved()->where('rating', $star)->count();
                        $percentage = $property->reviews_count > 0 ? ($count / $property->reviews_count) * 100 : 0;
                    @endphp
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm w-8">{{ $star }}★</span>
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        <span class="text-sm text-gray-600 w-8">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Review Form --}}
    @auth
        @if(!$property->reviews()->where('user_id', auth()->id())->exists())
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-4">Write a Review</h3>
                <form action="{{ route('reviews.store', $property) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Rating</label>
                        <div x-data="{ rating: 0 }" class="flex gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button"
                                        @click="rating = {{ $i }}"
                                        class="text-3xl transition">
                                    <span :class="rating >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'">★</span>
                                </button>
                            @endfor
                            <input type="hidden" name="rating" x-model="rating" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Your Review</label>
                        <textarea name="comment" rows="4" required
                                  class="w-full rounded-md border-gray-300"
                                  placeholder="Share your experience..."></textarea>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                        Submit Review
                    </button>
                </form>
            </div>
        @endif
    @else
        <div class="bg-gray-50 rounded-lg p-6 text-center">
            <p class="text-gray-600">
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login</a>
                to write a review
            </p>
        </div>
    @endauth

    {{-- Reviews List --}}
    <div class="space-y-4">
        @forelse($property->reviews()->approved()->latest()->get() as $review)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ $review->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($review->user->name) }}"
                             alt="{{ $review->user->name }}"
                             class="w-12 h-12 rounded-full">
                        <div>
                            <div class="font-semibold">{{ $review->user->name }}</div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <div class="flex">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                    @endfor
                                </div>
                                <span>•</span>
                                <span>{{ $review->created_at->diffForHumans() }}</span>
                                @if($review->is_verified_renter)
                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                        Verified Renter
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <p class="mt-3 text-gray-700">{{ $review->comment }}</p>

                {{-- Owner Response --}}
                @if($review->owner_response)
                    <div class="mt-4 ml-8 bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z"/>
                            </svg>
                            <span class="font-semibold text-sm">Owner Response</span>
                            <span class="text-xs text-gray-500">{{ $review->owner_responded_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-gray-700">{{ $review->owner_response }}</p>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                No reviews yet. Be the first to review!
            </div>
        @endforelse
    </div>
</div>
```

---

## 1️⃣5️⃣ Messaging System

### 💬 Message Model

```php
// app/Models/Message.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'property_id',
        'sender_id',
        'receiver_id',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }
}
```

### 📝 Message Service

```php
// app/Services/MessageService.php
namespace App\Services;

use App\Models\Message;
use App\Models\Property;
use App\Models\User;

class MessageService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function sendMessage(Property $property, User $sender, User $receiver, string $message): Message
    {
        $messageModel = Message::create([
            'property_id' => $property->id,
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => $message,
        ]);

        // Send notification
        $this->notificationService->notifyUserNewMessage($messageModel);

        return $messageModel;
    }

    public function getConversation(Property $property, User $user1, User $user2)
    {
        return Message::where('property_id', $property->id)
            ->where(function ($query) use ($user1, $user2) {
                $query->where(function ($q) use ($user1, $user2) {
                    $q->where('sender_id', $user1->id)
                      ->where('receiver_id', $user2->id);
                })->orWhere(function ($q) use ($user1, $user2) {
                    $q->where('sender_id', $user2->id)
                      ->where('receiver_id', $user1->id);
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getUserConversations(User $user)
    {
        return Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['property', 'sender', 'receiver'])
            ->latest()
            ->get()
            ->groupBy(function ($message) use ($user) {
                $otherUserId = $message->sender_id === $user->id
                    ? $message->receiver_id
                    : $message->sender_id;
                return $message->property_id . '-' . $otherUserId;
            })
            ->map(function ($messages) {
                return $messages->first();
            });
    }

    public function getUnreadCount(User $user): int
    {
        return Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();
    }
}
```

### 🎮 Message Controller

```php
// app/Http/Controllers/MessageController.php
namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Models\Property;
use App\Models\User;
use App\Services\MessageService;

class MessageController extends Controller
{
    public function __construct(
        private MessageService $messageService
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        $conversations = $this->messageService->getUserConversations(auth()->user());

        return view('messages.index', compact('conversations'));
    }

    public function show(Property $property, User $user)
    {
        $messages = $this->messageService->getConversation(
            $property,
            auth()->user(),
            $user
        );

        // Mark messages as read
        $messages->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->each->markAsRead();

        return view('messages.show', compact('property', 'user', 'messages'));
    }

    public function store(SendMessageRequest $request, Property $property)
    {
        $receiver = User::findOrFail($request->receiver_id);

        $message = $this->messageService->sendMessage(
            $property,
            auth()->user(),
            $receiver,
            $request->message
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'success' => true
            ]);
        }

        return back()->with('success', 'Message sent successfully');
    }
}
```

---

## 1️⃣6️⃣ Recommendation Engine

### 🤖 Recommendation Service

```php
// app/Services/RecommendationService.php
namespace App\Services;

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * Get similar properties based on multiple factors
     */
    public function getSimilarProperties(Property $property, int $limit = 6): Collection
    {
        return Property::where('id', '!=', $property->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(function ($query) use ($property) {
                // Same city (high priority)
                $query->where('city', $property->city)
                    // Same type
                    ->where('type', $property->type)
                    // Similar price range (±20%)
                    ->whereBetween('price', [
                        $property->price * 0.8,
                        $property->price * 1.2
                    ])
                    // Similar area (±30%)
                    ->whereBetween('area', [
                        $property->area * 0.7,
                        $property->area * 1.3
                    ]);
            })
            ->orWhere(function ($query) use ($property) {
                // Fallback: same city and type only
                $query->where('city', $property->city)
                    ->where('type', $property->type);
            })
            ->with(['owner', 'images'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Get personalized recommendations based on user behavior
     */
    public function getPersonalizedRecommendations(User $user, int $limit = 10): Collection
    {
        // Get user's favorite properties
        $favorites = $user->favorites()->pluck('properties.id');

        if ($favorites->isEmpty()) {
            // Return popular properties if no favorites
            return $this->getPopularProperties($limit);
        }

        // Analyze user preferences
        $preferences = $this->analyzeUserPreferences($user);

        return Property::where('status', 'approved')
            ->where('is_active', true)
            ->whereNotIn('id', $favorites)
            ->where(function ($query) use ($preferences) {
                if (!empty($preferences['cities'])) {
                    $query->whereIn('city', $preferences['cities']);
                }

                if (!empty($preferences['types'])) {
                    $query->whereIn('type', $preferences['types']);
                }

                if (isset($preferences['avg_price'])) {
                    $query->whereBetween('price', [
                        $preferences['avg_price'] * 0.7,
                        $preferences['avg_price'] * 1.3
                    ]);
                }
            })
            ->with(['owner', 'images'])
            ->orderBy('average_rating', 'desc')
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Analyze user preferences from favorites and views
     */
    private function analyzeUserPreferences(User $user): array
    {
        $favorites = $user->favorites;

        return [
            'cities' => $favorites->pluck('city')->unique()->values()->toArray(),
            'types' => $favorites->pluck('type')->unique()->values()->toArray(),
            'avg_price' => $favorites->avg('price'),
            'avg_area' => $favorites->avg('area'),
            'avg_bedrooms' => round($favorites->avg('bedrooms')),
        ];
    }

    /**
     * Get popular properties
     */
    public function getPopularProperties(int $limit = 10): Collection
    {
        return Property::where('status', 'approved')
            ->where('is_active', true)
            ->with(['owner', 'images'])
            ->orderBy('views_count', 'desc')
            ->orderBy('favorites_count', 'desc')
            ->orderBy('average_rating', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending properties (most viewed in last 7 days)
     */
    public function getTrendingProperties(int $limit = 10): Collection
    {
        // This requires a views tracking table with timestamps
        // For now, we'll use a simplified version
        return Property::where('status', 'approved')
            ->where('is_active', true)
            ->where('created_at', '>=', now()->subDays(7))
            ->with(['owner', 'images'])
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
```

---

## 1️⃣7️⃣ Notification System

### 🔔 Notification Service

```php
// app/Services/NotificationService.php
namespace App\Services;

use App\Models\Property;
use App\Models\Review;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewListingNotification;
use App\Notifications\PropertyApprovedNotification;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function notifyAdminNewListing(Property $property): void
    {
        $admins = User::role('admin')->get();

        Notification::send($admins, new \App\Notifications\AdminNewListingNotification($property));
    }

    public function notifyOwnerPropertyApproved(Property $property): void
    {
        $property->owner->notify(new PropertyApprovedNotification($property));
    }

    public function notifyOwnerPropertyRejected(Property $property, string $reason): void
    {
        $property->owner->notify(new \App\Notifications\PropertyRejectedNotification($property, $reason));
    }

    public function notifyUsersNewListing(Property $property): void
    {
        // Find users with saved searches matching this property
        $matchingUsers = $this->findUsersWithMatchingSavedSearches($property);

        Notification::send($matchingUsers, new NewListingNotification($property));
    }

    public function notifyOwnerNewReview(Property $property, Review $review): void
    {
        $property->owner->notify(new \App\Notifications\NewReviewNotification($review));
    }

    public function notifyUserReviewApproved(Review $review): void
    {
        $review->user->notify(new \App\Notifications\ReviewApprovedNotification($review));
    }

    public function notifyUserOwnerResponded(Review $review): void
    {
        $review->user->notify(new \App\Notifications\OwnerRespondedNotification($review));
    }

    public function notifyUserNewMessage(Message $message): void
    {
        $message->receiver->notify(new NewMessageNotification($message));
    }

    public function notifyUserPriceDropped(Property $property, float $oldPrice): void
    {
        // Find users who favorited this property
        $users = $property->favoritedBy;

        Notification::send($users, new \App\Notifications\PriceDropNotification($property, $oldPrice));
    }

    private function findUsersWithMatchingSavedSearches(Property $property): Collection
    {
        return User::whereHas('savedSearches', function ($query) use ($property) {
            $query->where('email_alerts', true)
                ->where(function ($q) use ($property) {
                    // Match city
                    $q->whereJsonContains('filters->city', $property->city)
                      // Match type
                      ->orWhereJsonContains('filters->type', $property->type)
                      // Match price range
                      ->orWhere(function ($priceQuery) use ($property) {
                          $priceQuery->where('filters->min_price', '<=', $property->price)
                                    ->where('filters->max_price', '>=', $property->price);
                      });
                });
        })->get();
    }
}
```

### 📧 Example Notification Class

```php
// app/Notifications/NewListingNotification.php
namespace App\Notifications;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewListingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Property $property
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Property Matching Your Search')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new property matching your saved search has been listed.')
            ->line('**' . $this->property->title . '**')
            ->line('Price: $' . number_format($this->property->price) . '/month')
            ->line('Location: ' . $this->property->city)
            ->action('View Property', route('properties.show', $this->property->slug))
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable): array
    {
        return [
            'property_id' => $this->property->id,
            'property_title' => $this->property->title,
            'property_slug' => $this->property->slug,
            'message' => 'New property matching your search: ' . $this->property->title,
        ];
    }
}
```

---

_This is Part 2 of the complete guide. The document continues with sections 18-28 covering Admin Dashboard, UI/UX Design, Security, SEO, API, Testing, MVP, Timeline, Monetization, Roadmap, and Deployment._

**Shall I continue with the remaining sections (Part 3)?**
