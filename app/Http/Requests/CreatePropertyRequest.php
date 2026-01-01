<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Mocked for scaffolding
    }
    
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric'],
            'city' => ['required', 'string'],
            // Add other rules as needed
        ];
    }
}
