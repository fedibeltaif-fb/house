@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<div class="relative bg-gray-900 overflow-hidden">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1920&q=80" alt="Apartment" class="w-full h-full object-cover opacity-40">
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40"></div>
    </div>
    
    <div class="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8 text-center text-white">
        <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl mb-6">
            Find the perfect place<br class="hidden sm:block"> to call home
        </h1>
        <p class="mt-6 text-xl text-gray-300 max-w-3xl mx-auto mb-10">
            Discover thousands of apartments, houses, and condos for rent. We connect you directly with landlords for a seamless rental experience.
        </p>
    </div>
</div>

<!-- Search Component -->
<x-search-bar />

<!-- Featured Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Featured Properties</h2>
            <p class="mt-2 text-gray-600">Hand-picked properties just for you</p>
        </div>
        <a href="{{ route('properties.index') }}" class="text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1 group">
            View all properties
            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        {{-- In a real app, this would loop via $featured variable --}}
        @forelse($featured ?? [] as $property)
            <x-property-card :property="$property" />
        @empty
            {{-- Fallback for scaffolding visualization --}}
            <x-property-card />
            <x-property-card />
            <x-property-card />
        @endforelse
    </div>
</div>

<!-- Features/Benefits -->
<div class="bg-white py-16 border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-12">
            <h2 class="text-base text-primary-600 font-semibold tracking-wide uppercase">Why Choose Us</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                The easiest way to rent
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div class="text-center p-6 bg-gray-50 rounded-xl">
                <div class="w-12 h-12 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Verified Listings</h3>
                <p class="mt-2 text-gray-500">Every property is verified to ensure you get exactly what you see.</p>
            </div>
            <div class="text-center p-6 bg-gray-50 rounded-xl">
                <div class="w-12 h-12 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">No Hidden Fees</h3>
                <p class="mt-2 text-gray-500">Transparent pricing with no surprise costs or hidden commissions.</p>
            </div>
            <div class="text-center p-6 bg-gray-50 rounded-xl">
                <div class="w-12 h-12 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Direct Communication</h3>
                <p class="mt-2 text-gray-500">Chat directly with landlords to ask questions and schedule viewings.</p>
            </div>
        </div>
    </div>
</div>
@endsection
