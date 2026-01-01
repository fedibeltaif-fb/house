<?php

namespace App\Services;

use App\Models\Property;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyService
{
    public function __construct(
        private PropertyRepositoryInterface $propertyRepository,
        private ImageService $imageService
    ) {}
    
    public function createProperty(array $data, array $images = []): Property
    {
        return DB::transaction(function () use ($data, $images) {
            $data['slug'] = Str::slug($data['title']);
            $data['status'] = 'pending';
            
            $property = $this->propertyRepository->create($data);
            
            if (!empty($data['amenities'])) {
                $property->amenities()->attach($data['amenities']);
            }
            
            if (!empty($images)) {
                $this->imageService->uploadPropertyImages($property, $images);
            }
            
            return $property;
        });
    }
    
    public function updateProperty(int $id, array $data, array $newImages = []): Property
    {
        return DB::transaction(function () use ($id, $data, $newImages) {
            $this->propertyRepository->update($id, $data);
            $property = $this->propertyRepository->findById($id);
            
            if (isset($data['amenities'])) {
                $property->amenities()->sync($data['amenities']);
            }
            
            if (!empty($newImages)) {
                $this->imageService->uploadPropertyImages($property, $newImages);
            }
            
            return $property;
        });
    }
    
    public function deleteProperty(int $id): bool
    {
        return $this->propertyRepository->delete($id);
    }
}
