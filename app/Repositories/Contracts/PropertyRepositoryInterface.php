<?php

namespace App\Repositories\Contracts;

use App\Models\Property;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PropertyRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?Property;
    public function findBySlug(string $slug): ?Property;
    public function create(array $data): Property;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function search(array $filters): LengthAwarePaginator;
    public function getFeatured(int $limit = 10): Collection;
    public function getByOwner(int $ownerId): Collection;
    public function getSimilar(Property $property, int $limit = 6): Collection;
}
