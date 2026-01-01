<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'user_id', 'rating', 'comment',
        'owner_response', 'owner_responded_at',
        'is_verified_renter', 'is_approved'
    ];

    protected $casts = [
        'owner_responded_at' => 'datetime',
        'is_verified_renter' => 'boolean',
        'is_approved' => 'boolean',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
