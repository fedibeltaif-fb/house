<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
// use Intervention\Image\Facades\Image; // Assuming package installed later

class ImageService
{
    public function uploadPropertyImages(Property $property, array $images): void
    {
        foreach ($images as $index => $image) {
            $this->uploadPropertyImage($property, $image, $index === 0);
        }
    }
    
    public function uploadPropertyImage(Property $property, $image, bool $isPrimary = false): PropertyImage
    {
        // Mocking upload logic since we don't have the actual image library installed yet
        $filename = uniqid() . '_' . time() . '.jpg';
        $path = "properties/{$property->id}/{$filename}";
        
        // Storage::disk('public')->put($path, file_get_contents($image));
        
        return PropertyImage::create([
            'property_id' => $property->id,
            'image_path' => $path,
            'thumbnail_path' => $path, // Mock
            'order' => $property->images()->count(),
            'is_primary' => $isPrimary,
        ]);
    }
}
