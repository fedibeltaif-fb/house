<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePropertyRequest;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(
        private PropertyService $propertyService
    ) {}
    
    public function index()
    {
        // Mocked response
        return view('properties.index');
    }
    
    public function show($slug)
    {
        // Mocked response
        return view('properties.show');
    }
    
    public function store(CreatePropertyRequest $request)
    {
        $this->propertyService->createProperty($request->validated());
        return redirect()->back();
    }
}
