<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Repositories\Contracts\PropertyRepositoryInterface;

class SearchController extends Controller
{
    public function __construct(
        private PropertyRepositoryInterface $propertyRepository
    ) {}
    
    public function index(SearchRequest $request)
    {
        $results = $this->propertyRepository->search($request->validated());
        return view('properties.search', compact('results'));
    }
}
