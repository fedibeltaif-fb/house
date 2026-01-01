@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen pb-12">
    <!-- Image Gallery -->
    <div class="bg-gray-900 h-[60vh] relative group">
        <img src="{{ $property->primaryImage->image_path ?? 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80' }}" 
             alt="{{ $property->title ?? 'Property' }}" 
             class="w-full h-full object-cover">
        
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/60 to-transparent"></div>
        
        <div class="absolute bottom-0 left-0 right-0 p-8 max-w-7xl mx-auto">
            <div class="flex justify-between items-end">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">{{ $property->title ?? 'Modern Apartment in City Center' }}</h1>
                    <p class="text-lg text-gray-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $property->address ?? '123 Main St' }}, {{ $property->city ?? 'New York' }}
                    </p>
                </div>
                <div class="hidden md:block">
                    <span class="text-3xl font-bold text-white">${{ number_format($property->price ?? 2500) }}</span>
                    <span class="text-gray-300">/mo</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Quick Stats Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 flex justify-between items-center text-center divide-x divide-gray-100">
                    <div class="flex-1">
                        <span class="block text-2xl font-bold text-gray-900">{{ $property->bedrooms ?? 2 }}</span>
                        <span class="text-sm text-gray-500">Bedrooms</span>
                    </div>
                    <div class="flex-1">
                        <span class="block text-2xl font-bold text-gray-900">{{ $property->bathrooms ?? 2 }}</span>
                        <span class="text-sm text-gray-500">Bathrooms</span>
                    </div>
                    <div class="flex-1">
                        <span class="block text-2xl font-bold text-gray-900">{{ $property->area ?? 1200 }}</span>
                        <span class="text-sm text-gray-500">sqft</span>
                    </div>
                    <div class="flex-1">
                        <span class="block text-2xl font-bold text-gray-900">{{ $property->type ?? 'Apartment' }}</span>
                        <span class="text-sm text-gray-500">Type</span>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">About this property</h2>
                    <div class="prose max-w-none text-gray-600">
                        <p>{!! nl2br(e($property->description ?? "Experience luxury living in this stunning apartment located in the heart of the city. Featuring modern amenities, spacious rooms, and breathtaking views, this property is perfect for professionals and families alike.\n\nThe apartment comes fully furnished with state-of-the-art appliances, hardwood floors, and large windows that flood the space with natural light.")) !!}</p>
                    </div>
                </div>

                <!-- Amenities -->
                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Amenities</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($property->amenities ?? ['Wifi', 'Parking', 'Pool', 'Gym', 'AC', 'Heater'] as $amenity)
                            <div class="flex items-center gap-2 text-gray-600">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>{{ is_string($amenity) ? $amenity : $amenity->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Map (Placeholder) -->
                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Location</h2>
                    <div class="bg-gray-100 rounded-lg h-64 flex items-center justify-center text-gray-400">
                        <span>Map View (Integration pending)</span>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    <!-- Price Card (Mobile only shown in header) -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                        <div class="mb-6">
                            <span class="text-3xl font-bold text-gray-900">${{ number_format($property->price ?? 2500) }}</span>
                            <span class="text-gray-500">/mo</span>
                            <div class="text-sm text-green-600 font-medium mt-1">Available Now</div>
                        </div>

                        <form class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                                <textarea rows="3" class="w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500" placeholder="Hi, I'm interested in this property..."></textarea>
                            </div>
                            <button type="submit" class="w-full bg-primary-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-primary-700 transition shadow-md">
                                Request Tour
                            </button>
                            <button type="button" class="w-full bg-white text-primary-700 font-bold py-3 px-4 rounded-lg border-2 border-primary-100 hover:border-primary-200 hover:bg-primary-50 transition">
                                Contact Landlord
                            </button>
                        </form>
                    </div>

                    <!-- Landlord Profile -->
                    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex-shrink-0"></div>
                        <div>
                            <div class="font-bold text-gray-900">{{ $property->owner->name ?? 'John Doe' }}</div>
                            <div class="text-sm text-gray-500">Property Owner</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
