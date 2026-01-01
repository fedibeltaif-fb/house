<div class="w-full max-w-4xl mx-auto bg-white rounded-lg shadow-xl p-6 -mt-16 relative z-10">
    <form action="{{ route('properties.search') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="col-span-1 md:col-span-2 relative">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Location</label>
            <div class="relative">
                <input type="text" name="city" placeholder="Where do you want to live?" class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
        
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Type</label>
            <select name="type" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition bg-white appearance-none">
                <option value="">Any Type</option>
                <option value="apartment">Apartment</option>
                <option value="house">House</option>
                <option value="studio">Studio</option>
                <option value="villa">Villa</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition duration-200">
                Search Properties
            </button>
        </div>
    </form>
</div>
