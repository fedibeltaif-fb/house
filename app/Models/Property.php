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
