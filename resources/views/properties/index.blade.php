@extends('layouts.app')

@section('content')
<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">All Properties</h1>
        
        <!-- Filters -->
        <form action="{{ route('properties.index') }}" method="GET" class="flex flex-wrap gap-4 items-end bg-gray-50 p-4 rounded-lg border border-gray-100">
            <div class="flex-grow min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Location</label>
                <input type="text" name="city" value="{{ request('city') }}" placeholder="City or District" class="w-full text-sm border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <div class="w-[150px]">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Type</label>
                <select name="type" class="w-full text-sm border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Any</option>
                    <option value="apartment" {{ request('type') == 'apartment' ? 'selected' : '' }}>Apartment</option>
                    <option value="house" {{ request('type') == 'house' ? 'selected' : '' }}>House</option>
                </select>
            </div>

            <div class="w-[150px]">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Price Range</label>
                <select name="price_range" class="w-full text-sm border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Any Price</option>
                    <option value="0-1000">Under $1,000</option>
                    <option value="1000-2000">$1,000 - $2,000</option>
                    <option value="2000+">$2,000+</option>
                </select>
            </div>

            <button type="submit" class="bg-gray-900 text-white px-6 py-2 rounded-md text-sm font-medium hover:bg-gray-800 transition shadow-sm h-[38px]">
                Filter Matches
            </button>
        </form>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Results Info -->
    <div class="mb-6 flex justify-between items-center">
        <p class="text-gray-600">
            Showing <span class="font-semibold text-gray-900">{{ $properties->count() ?? 0 }}</span> properties
        </p>
        
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Sort by:</span>
            <select class="text-sm border-none bg-transparent font-medium text-gray-900 focus:ring-0 cursor-pointer">
                <option>Newest</option>
                <option>Price: Low to High</option>
                <option>Price: High to Low</option>
            </select>
        </div>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($properties ?? [] as $property)
            <x-property-card :property="$property" />
        @empty
            @for($i=0; $i<6; $i++)
                 <x-property-card /> {{-- remove in production --}}
            @endfor
            {{-- 
            <div class="col-span-full text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No properties found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your search criteria.</p>
            </div>
            --}}
        @endforelse
    </div>
    
    <!-- Pagination -->
    <div class="mt-12">
        {{-- {{ $properties->links() }} --}}
    </div>
</div>
@endsection
