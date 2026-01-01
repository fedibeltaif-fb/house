@props(['property'])

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden card-hover group">
    <!-- Image -->
    <div class="relative h-48 sm:h-56 bg-gray-200 overflow-hidden">
        <a href="{{ route('properties.show', $property->slug ?? '#') }}">
            <img src="{{ $property->primaryImage->image_path ?? 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80' }}" 
                 alt="{{ $property->title ?? 'Property' }}"
                 class="w-full h-full object-cover transition duration-300 group-hover:scale-110">
        </a>
        
        <div class="absolute top-3 left-3 flex gap-2">
            <span class="bg-white/90 backdrop-blur text-xs font-semibold px-2 py-1 rounded-md shadow-sm uppercase tracking-wide">
                {{ $property->type ?? 'Apartment' }}
            </span>
            @if($property->is_featured ?? false)
                <span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded-md shadow-sm flex items-center gap-1">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Featured
                </span>
            @endif
        </div>

        <button class="absolute top-3 right-3 p-2 rounded-full bg-white/90 hover:bg-white text-gray-500 hover:text-red-500 shadow-sm transition backdrop-blur">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
        </button>
    </div>

    <!-- Content -->
    <div class="p-4 sm:p-5">
        <div class="flex justify-between items-start mb-2">
            <h3 class="text-lg font-bold text-gray-900 line-clamp-1 group-hover:text-primary-600 transition">
                <a href="{{ route('properties.show', $property->slug ?? '#') }}">{{ $property->title ?? 'Modern Apartment in City Center' }}</a>
            </h3>
        </div>
        
        <p class="text-gray-500 text-sm mb-4 flex items-center gap-1">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ $property->city ?? 'New York' }}, {{ $property->state ?? 'NY' }}
        </p>

        <div class="flex items-center gap-4 mb-4 text-sm text-gray-600">
            <div class="flex items-center gap-1">
                <span class="font-semibold text-gray-900">{{ $property->bedrooms ?? 2 }}</span>
                <span class="text-xs">Beds</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="font-semibold text-gray-900">{{ $property->bathrooms ?? 2 }}</span>
                <span class="text-xs">Baths</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="font-semibold text-gray-900">{{ $property->area ?? 1200 }}</span>
                <span class="text-xs">sqft</span>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-4 flex items-center justify-between">
            <div>
                <span class="text-2xl font-bold text-primary-600">${{ number_format($property->price ?? 2500) }}</span>
                <span class="text-sm text-gray-500">/mo</span>
            </div>
            <a href="{{ route('properties.show', $property->slug ?? '#') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700">View Details →</a>
        </div>
    </div>
</div>
