<?php

namespace App\Repositories;

use App\Models\Property;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PropertyRepository implements PropertyRepositoryInterface
{
    public function all(): Collection
    {
        return Property::with(['owner', 'images'])
            ->where('status', 'approved')
            ->where('is_active', true)
            ->latest()
            ->get();
    }
    
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Property::with(['owner', 'images'])
            ->where('status', 'approved')
            ->where('is_active', true)
            ->latest()
            ->paginate($perPage);
    }
    
    public function findById(int $id): ?Property
    {
        return Property::with(['owner', 'images', 'amenities', 'reviews'])
            ->findOrFail($id);
    }
    
    public function findBySlug(string $slug): ?Property
    {
        return Property::with(['owner', 'images', 'amenities', 'reviews'])
            ->where('slug', $slug)
            ->firstOrFail();
    }
    
    public function create(array $data): Property
    {
        return Property::create($data);
    }
    
    public function update(int $id, array $data): bool
    {
        $property = Property::findOrFail($id);
        return $property->update($data);
    }
    
    public function delete(int $id): bool
    {
        $property = Property::findOrFail($id);
        return $property->delete();
    }
    
    public function search(array $filters): LengthAwarePaginator
    {
        $query = Property::query()
            ->with(['owner', 'images'])
            ->where('status', 'approved')
            ->where('is_active', true);
        
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }
        
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        if (!empty($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', $filters['bedrooms']);
        }
        
        // Sorting
        $query->latest();
        
        return $query->paginate($filters['per_page'] ?? 15);
    }
    
    public function getFeatured(int $limit = 10): Collection
    {
        return Property::with(['owner', 'images'])
            ->where('is_featured', true)
            ->where('featured_until', '>', now())
            ->limit($limit)
            ->get();
    }
    
    public function getByOwner(int $ownerId): Collection
    {
        return Property::with(['images'])->where('owner_id', $ownerId)->get();
    }
    
    public function getSimilar(Property $property, int $limit = 6): Collection
    {
        return Property::with(['owner', 'images'])
            ->where('id', '!=', $property->id)
            ->where('city', $property->city)
            ->limit($limit)
            ->get();
    }
}
