<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric'],
            // Add other rules
        ];
    }
}
