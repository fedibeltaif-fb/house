<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'city' => ['nullable', 'string'],
            'min_price' => ['nullable', 'numeric'],
            'max_price' => ['nullable', 'numeric'],
            // Add other rules as needed
        ];
    }
}
